defmodule PhoenixApi.RateLimiter do
  @moduledoc """
  Rate limiter using OTP (GenServer) and ETS.
  Implements sliding window rate limiting.
  """
  use GenServer

  @table :rate_limit_table
  @user_limit 5
  @user_window 600 # 10 minutes in seconds
  @global_limit 1000
  @global_window 3600 # 1 hour in seconds

  # Client API

  def start_link(opts) do
    GenServer.start_link(__MODULE__, opts, name: __MODULE__)
  end

  @doc """
  Checks if the request is within rate limits for the given user.
  Returns :ok or {:error, reason}
  """
  def check_rate_limit(user_id) do
    GenServer.call(__MODULE__, {:check, user_id})
  end

  @doc """
  Checks only global rate limit.
  """
  def check_global_limit do
    GenServer.call(__MODULE__, :check_global)
  end

  @doc """
  Clears all rate limit data.
  """
  def clear do
    GenServer.cast(__MODULE__, :clear)
  end

  # Server Callbacks

  @impl true
  def init(_opts) do
    :ets.new(@table, [:named_table, :set, :protected, read_concurrency: true])
    {:ok, %{}}
  end

  @impl true
  def handle_cast(:clear, state) do
    :ets.delete_all_objects(@table)
    {:noreply, state}
  end

  @impl true
  def handle_call(:check_global, _from, state) do
    now = System.system_time(:second)
    global_ts = get_timestamps(:global)
    pruned_global = prune_timestamps(global_ts, now, @global_window)

    if length(pruned_global) >= @global_limit do
      {:reply, {:error, :global_rate_limit_exceeded}, state}
    else
      new_global = [now | pruned_global]
      :ets.insert(@table, {:global, new_global})
      {:reply, :ok, state}
    end
  end

  @impl true
  def handle_call({:check, user_id}, _from, state) do
    now = System.system_time(:second)

    # 1. Check Global Limit
    global_ts = get_timestamps(:global)
    pruned_global = prune_timestamps(global_ts, now, @global_window)

    if length(pruned_global) >= @global_limit do
      {:reply, {:error, :global_rate_limit_exceeded}, state}
    else
      # 2. Check User Limit (if user_id provided)
      case user_id do
        nil ->
          new_global = [now | pruned_global]
          :ets.insert(@table, {:global, new_global})
          {:reply, :ok, state}

        id ->
          user_key = {:user, id}
          user_ts = get_timestamps(user_key)
          pruned_user = prune_timestamps(user_ts, now, @user_window)

          if length(pruned_user) >= @user_limit do
            {:reply, {:error, :user_rate_limit_exceeded}, state}
          else
            new_global = [now | pruned_global]
            new_user = [now | pruned_user]

            :ets.insert(@table, {:global, new_global})
            :ets.insert(@table, {user_key, new_user})

            {:reply, :ok, state}
          end
      end
    end
  end

  # Helpers

  defp get_timestamps(key) do
    case :ets.lookup(@table, key) do
      [{^key, ts}] -> ts
      [] -> []
    end
  end

  defp prune_timestamps(timestamps, now, window) do
    Enum.filter(timestamps, fn ts -> now - ts < window end)
  end
end
