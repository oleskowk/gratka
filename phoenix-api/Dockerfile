FROM elixir:1.15-alpine AS base

RUN apk add --no-cache build-base git nodejs npm inotify-tools bash

ARG UID=1000
ARG GID=1000

RUN addgroup -g $GID appgroup && \
    adduser -u $UID -G appgroup -D appuser

WORKDIR /app

FROM base AS dev

ENV MIX_ENV=dev

COPY ./entrypoint.sh /entrypoint.sh
USER root
RUN chmod +x /entrypoint.sh
USER appuser

RUN mix local.hex --force && mix local.rebar --force

EXPOSE 4000

ENTRYPOINT ["/entrypoint.sh"]

FROM base AS prod

ENV MIX_ENV=prod

COPY mix.exs mix.lock ./
COPY config ./config

RUN mix deps.get --only prod && mix deps.compile

COPY lib lib
COPY priv priv

RUN mix compile && mix release

EXPOSE 4000

CMD ["_build/prod/rel/phoenix_api/bin/phoenix_api", "start"]
