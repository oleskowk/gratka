defmodule PhoenixApiWeb.Plugs.RateLimit do
  @moduledoc """
  Plug to enforce rate limits using PhoenixApi.RateLimiter.
  """
  import Plug.Conn
  import Phoenix.Controller

  alias PhoenixApi.RateLimiter

  def init(opts), do: opts

  def call(conn, _opts) do
    user_id =
      case conn.assigns[:current_user] do
        %{id: id} -> id
        _ -> nil
      end

    case RateLimiter.check_rate_limit(user_id) do
      :ok ->
        conn

      {:error, _reason} ->
        conn
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429")
        |> halt()
    end
  end
end
