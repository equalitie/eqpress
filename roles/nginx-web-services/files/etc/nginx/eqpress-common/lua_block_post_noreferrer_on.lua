if ngx.var.http_referer == nil and ngx.re.match(ngx.var.request_method, "POST") then
    return ngx.exit(ngx.HTTP_FORBIDDEN);
end
