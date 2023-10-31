FROM redis:6.2-alpine

ADD redis.conf /etc/redis.conf

CMD ["redis-server", "/etc/redis.conf", "--save", "20", "1", "--loglevel", "warning"]
