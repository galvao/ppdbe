FROM postgres:17

USER root

RUN apt -y update
RUN apt -y install sudo vim libnss-wrapper

USER postgres
COPY ./setup/. /docker-entrypoint-initdb.d
