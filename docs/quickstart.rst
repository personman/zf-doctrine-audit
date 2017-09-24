Quickstart
==========

To see this in action and begin exploring what this repository can do, follow these steps:

1. Clone the repository outside of a project and cd into the repository directory.
2. Run ``docker-compose up -d`` to start a container to work in.  You may need to install Docker if you're not already using it.
3. Run ``docker/connect`` to connect to a shell inside the container.
4. Run ``composer install`` to install required libraries.
5. Run ``vendor/bin/phpunit`` to execute the unit tests.  After the unit tests have ran the databases they use still exist. 
6. Connect to mysql with ``mysql -u root -h mysql test``

Explore the data in the test database.  The audit database has the audit trails for the test data.  Manipulating data in the test database is immediatly audited in the audit database.

