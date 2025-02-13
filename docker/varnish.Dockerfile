FROM varnish:6.0

COPY ./docker/etc/varnish/default.vcl /etc/varnish/default.vcl
