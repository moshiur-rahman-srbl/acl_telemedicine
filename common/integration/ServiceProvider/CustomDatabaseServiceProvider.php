<?php

namespace common\integration\ServiceProvider;

use common\integration\Override\CustomMySqlConnection;
use common\integration\Override\CustomPostgresConnection;
use common\integration\Override\CustomSQLiteConnection;
use common\integration\Override\CustomSqlServerConnection;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Query\Grammars\MySqlGrammar as MySqlQueryGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar as PostgresQueryGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as SQLiteQueryGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as SqlServerQueryGrammar;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as MySqlSchemaGrammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as PostgresSchemaGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SQLiteSchemaGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as SqlServerSchemaGrammar;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class CustomDatabaseServiceProvider extends ServiceProvider
{
    /**
     * The array of resolved Faker instances.
     *
     * @var array
     */
    protected static $fakers = [];

    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    public function register(): void
    {
        Model::clearBootedModels();

        $this->registerConnectionServices();
        $this->registerEloquentFactory();
        $this->registerQueueableEntityResolver();
    }

    protected function registerConnectionServices(): void
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            $db_driver = $this->app['config']->get('database.default');
            $db_manager = new DatabaseManager($app, $app['db.factory']);
            $db_manager->extend($db_driver, function($config, $name) use ($app) {
                //Create default connection from factory
                $default_connection = $app['db.factory']->make($config, $name);

                //Instantiate our connection with the default connection data
                $custom_connection = $this->getCustomConnection(
                    fn() => $default_connection->getPdo(),
                    $default_connection->getDatabaseName(),
                    $default_connection->getTablePrefix(),
                    $config
                );

                //Set the appropriate grammar object
                $custom_connection->setQueryGrammar(
                    $this->getQueryGrammarDynamically()
                );
                $custom_connection->setSchemaGrammar(
                    $this->getSchemaGrammarDynamically()
                );
                return $custom_connection;
            });
            return $db_manager;
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });

        $this->app->bind('db.schema', function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });

        $this->app->singleton('db.transactions', function () {
            return new DatabaseTransactionsManager;
        });
    }

    protected function registerEloquentFactory(): void
    {
        $this->app->singleton(FakerGenerator::class, function ($app, $parameters) {
            $locale = $parameters['locale'] ?? $app['config']->get('app.faker_locale', 'en_US');

            if (! isset(static::$fakers[$locale])) {
                static::$fakers[$locale] = FakerFactory::create($locale);
            }

            static::$fakers[$locale]->unique(true);

            return static::$fakers[$locale];
        });
    }

    protected function registerQueueableEntityResolver(): void
    {
        $this->app->singleton(EntityResolver::class, function () {
            return new QueueEntityResolver;
        });
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getQueryGrammarDynamically(): MySqlQueryGrammar|PostgresQueryGrammar|SqlServerQueryGrammar|SQLiteQueryGrammar
    {
        $db_driver = $this->app['config']->get('database.default');
        return match ($db_driver) {
            $this->app['config']->get('database.connections.mysql.driver') => new MySqlQueryGrammar(),
            $this->app['config']->get('database.connections.pgsql.driver') => new PostgresQueryGrammar(),
            $this->app['config']->get('database.connections.sqlite.driver') => new SQLiteQueryGrammar(),
            $this->app['config']->get('database.connections.sqlsrv.driver') => new SqlServerQueryGrammar(),
            default => throw new InvalidArgumentException("Unsupported driver [$db_driver]."),
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getSchemaGrammarDynamically(): MySqlSchemaGrammar|PostgresSchemaGrammar|SqlServerSchemaGrammar|SQLiteSchemaGrammar
    {
        $db_driver = $this->app['config']->get('database.default');
        return match ($db_driver) {
            $this->app['config']->get('database.connections.mysql.driver') => new MySqlSchemaGrammar(),
            $this->app['config']->get('database.connections.pgsql.driver') => new PostgresSchemaGrammar(),
            $this->app['config']->get('database.connections.sqlite.driver') => new SQLiteSchemaGrammar(),
            $this->app['config']->get('database.connections.sqlsrv.driver') => new SqlServerSchemaGrammar(),
            default => throw new InvalidArgumentException("Unsupported driver [$db_driver]."),
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getCustomConnection($getPdo, $getDatabaseName, $getTablePrefix, $config): CustomMySqlConnection|CustomSqlServerConnection|CustomPostgresConnection|CustomSQLiteConnection
    {
        $db_driver = $this->app['config']->get('database.default');
        return match ($db_driver) {
            $this->app['config']->get('database.connections.mysql.driver') => new CustomMySqlConnection($getPdo, $getDatabaseName, $getTablePrefix, $config),
            $this->app['config']->get('database.connections.pgsql.driver') => new CustomPostgresConnection($getPdo, $getDatabaseName, $getTablePrefix, $config),
            $this->app['config']->get('database.connections.sqlite.driver') => new CustomSQLiteConnection($getPdo, $getDatabaseName, $getTablePrefix, $config),
            $this->app['config']->get('database.connections.sqlsrv.driver') => new CustomSqlServerConnection($getPdo, $getDatabaseName, $getTablePrefix, $config),
            default => throw new InvalidArgumentException("Unsupported driver [$db_driver]."),
        };
    }
}
