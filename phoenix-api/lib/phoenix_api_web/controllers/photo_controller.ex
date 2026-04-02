defmodule PhoenixApiWeb.PhotoController do
  use PhoenixApiWeb, :controller

  alias PhoenixApi.Media

  plug PhoenixApiWeb.Plugs.Authenticate
  plug PhoenixApiWeb.Plugs.RateLimit when action in [:index]

  def index(conn, _params) do
    current_user = conn.assigns.current_user

    photos = Media.list_user_photos(current_user.id)

    json(conn, %{photos: photos})
  end
end
