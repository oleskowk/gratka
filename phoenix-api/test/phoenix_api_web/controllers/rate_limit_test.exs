defmodule PhoenixApiWeb.RateLimitTest do
  use PhoenixApiWeb.ConnCase

  alias PhoenixApi.Repo
  alias PhoenixApi.Accounts.User

  setup do
    # Clear the rate limit table before each test to ensure isolation
    PhoenixApi.RateLimiter.clear()

    user =
      %User{}
      |> User.changeset(%{api_token: "test_token_rl_1"})
      |> Repo.insert!()

    {:ok, user: user}
  end

  describe "User Rate Limiting" do
    test "allows up to 5 requests per 10 minutes for an individual user", %{conn: conn, user: user} do
      # First 5 requests should succeed
      for _ <- 1..5 do
        resp_conn =
          conn
          |> put_req_header("access-token", user.api_token)
          |> get("/api/photos")

        assert json_response(resp_conn, 200)
      end

      # 6th request should be rate limited
      resp_conn =
        conn
        |> put_req_header("access-token", user.api_token)
        |> get("/api/photos")

      assert json_response(resp_conn, 429) == %{"errors" => %{"detail" => "Too Many Requests"}}
    end

    test "different users have independent rate limits", %{conn: conn, user: user1} do
      user2 =
        %User{}
        |> User.changeset(%{api_token: "test_token_rl_2"})
        |> Repo.insert!()

      # Exhaust user1's limit
      for _ <- 1..5 do
        conn
        |> put_req_header("access-token", user1.api_token)
        |> get("/api/photos")
      end

      # user1 is blocked
      resp1 =
        conn
        |> put_req_header("access-token", user1.api_token)
        |> get("/api/photos")
      assert json_response(resp1, 429)

      # user2 should still be allowed (independent limit)
      resp2 =
        conn
        |> put_req_header("access-token", user2.api_token)
        |> get("/api/photos")
      assert json_response(resp2, 200)
    end
  end
end
