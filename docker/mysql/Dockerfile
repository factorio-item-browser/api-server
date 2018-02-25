FROM mysql
LABEL maintainer="BluePsyduck <bluepsyduck@gmx.com>"

ENV MYSQL_ROOT_PASSWORD root

ADD ./setup-database.sql /docker-entrypoint-initdb.d

EXPOSE 3306