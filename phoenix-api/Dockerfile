FROM elixir:1.15-alpine AS base

RUN apk add --no-cache bash openssl ncurses-libs libstdc++

RUN mix local.hex --force && \
    mix local.rebar --force

WORKDIR /app

FROM base AS dev

RUN apk add --no-cache build-base git inotify-tools

ENV MIX_ENV=dev

ARG UID=1000
ARG GID=1000

RUN addgroup -g $GID appgroup && \
    adduser -u $UID -G appgroup -D appuser

COPY ./entrypoint.sh /entrypoint.sh
USER root
RUN chmod +x /entrypoint.sh
USER appuser

EXPOSE 4000

ENTRYPOINT ["/entrypoint.sh"]

FROM base AS builder

RUN apk add --no-cache build-base git

ENV MIX_ENV=prod

COPY mix.exs mix.lock ./
COPY config ./config

RUN mix deps.get --only prod && \
    mix deps.compile

COPY lib lib
COPY priv priv

RUN mix compile && \
    mix release

FROM alpine:3.18 AS prod

RUN apk add --no-cache bash openssl ncurses-libs libstdc++

WORKDIR /app

RUN adduser -D appuser
USER appuser

# Copy only the compiled release
COPY --from=builder --chown=appuser:appuser /app/_build/prod/rel/phoenix_api ./

EXPOSE 4000

CMD ["bin/phoenix_api", "start"]
