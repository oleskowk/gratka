defmodule PhoenixApi.Media do
  @moduledoc """
  The Media context.
  """

  import Ecto.Query, warn: false
  alias PhoenixApi.Repo
  alias PhoenixApi.Media.Photo

  @doc """
  Returns a list of photos for the given user ID.

  Only includes essential fields for the listing.
  """
  def list_user_photos(user_id) do
    Photo
    |> where([p], p.user_id == ^user_id)
    |> select([p], %{id: p.id, photo_url: p.photo_url})
    |> Repo.all()
  end
end
