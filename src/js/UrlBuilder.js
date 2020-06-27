class UrlBuilder {
    constructor(name, absolute, routerObject) {

        this.name = name;
        this.router = routesObject;
        this.route = this.router.namedRoutes[this.name];

        if (typeof this.name === 'undefined') {
            throw new Error('Router Error: You must provide a route name');
        } else if (typeof this.route === 'undefined') {
            throw new Error(`Router Error: route '${this.name}' is not found in the route list`);
        }

        this.absolute = typeof absolute === 'undefined' ? true : absolute;
        this.domain = this.setDomain();
        this.path = this.route.uri.replace(/^\//, '');
    }

    setDomain() {
        if (!this.absolute)
            return '/';

        if (!this.route.domain)
            return this.router.baseUrl.replace(/\/?$/, '/');

        let host = (this.route.domain || this.router.baseDomain).replace(/\/+$/, '');

        if (this.router.basePort) {
            host = `${host}:${this.router.basePort}`;
        }

        return this.router.baseProtocol + '://' + host + '/';
    }

    construct() {
        return this.domain + this.path
    }
}

export default UrlBuilder;
