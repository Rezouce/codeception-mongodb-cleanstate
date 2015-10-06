## What's this?
It's a Codeception module to ensure the MongoDB databases are dropped before each test to ensure a clean state.

## How to?
To add it to your project run `composer require Rezouce/codeception-mongodb-cleanstate`

You can then add *MongoDbCleanState* to your Codeception configuration file in the modules enabled section:
`
modules:
    enabled:
        - MongoDbCleanState
`
