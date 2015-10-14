## What's this?
It's a Codeception module to ensure the MongoDB databases are dropped before each test to ensure a clean state.

## How to?
To add it to your project run `composer require rezouce/codeception-mongodb-cleanstate`

You can then add *MongoDbCleanState* to your Codeception configuration file in the modules enabled section:
`
modules:
    enabled:
        - MongoDbCleanState
`
## License

This library is open-sourced software licensed under the MIT license
