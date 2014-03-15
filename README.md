cartesius
=========

Cartesius is a Server-Client stack for a Common Operating Picture (COP) platform.

It uses the Hot Towel stack for the client which includes:

* Knockout
* Durandal
* Breeze
* Kendo for the ui

The server stack currently uses:

* PostgreSQL
* PHP
* Slim
* Paris/Idiorm

On the server side there is a custom written adapter to serve data according to the ODATA/WebApi specification to make it easier to interact with Breeze.
The stack also includes subclasses of Paris/Idiorm which add extra features such as save as JSON/XML and autogeneration of metadata for database tables.

