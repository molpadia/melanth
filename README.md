# melanth
Melanth is a open source web application framework.
It's consist of a lightweight web server which can be used for development and testing.

## Installing Melanth
Before using Melanth, make sure you have Composer installed on your machine.
```
composer require melanth-web/melanth
```

Aftering installing Melanth, the following dirctory configuration you should configure in your web server application
```
- {your application}
    - app
    - bootstrap
    - config
    - routes
```

- `app` handle al of the incoming HTTP requests entering your web application.
- `bootstrap` stores all app configuration into a cached file to make application load faster.
- `config` define all of your configuration
- `routes` configure static routes to dispatch incoming request to the controller
