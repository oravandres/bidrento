# Bidrento Home Assignment

## Property Tree Management System

### Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture Design](#architecture-design)
  - [Overall Structure](#overall-structure)
  - [Component Diagram](#component-diagram)
  - [Database Diagram](#database-diagram)
  - [Data Flow](#data-flow)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Set up the environment](#set-up-the-environment)
  - [Clone the repository](#clone-the-repository)
  - [Install API dependencies](#install-api-dependencies)
  - [Run the database migrations](#run-the-database-migrations)
  - [Load database fixtures](#load-database-fixtures)
  - [Start the server](#start-the-server)
  - [Install FE dependencies](#install-fe-dependencies)
  - [Run FE application](#run-fe-application)
- [API Endpoints](#api-endpoints)
  - [Retrieve Full Property Tree](#retrieve-full-property-tree)
  - [Retrieve Specific Property Details](#retrieve-specific-property-details)
  - [Add New Property](#add-new-property)
  - [Add new property under an existing property in the tree](#add-new-property-under-an-existing-property-in-the-tree)
- [Testing](#testing)
  - [Create test database and run migrations](#create-test-database-and-run-migrations)
  - [Running tests](#running-tests)

### Overview

This system is designed to manage a hierarchical tree of properties, such as buildings and parking spaces, which can be abstractly represented as nodes within a tree structure. The service allows for the creation, deletion, retrieval, and organization of these properties in a manner that supports multiple levels and relationships, such as parent, child, and sibling connections.

Deletion of data is handled via soft deletes, meaning data is never permanently deleted but marked as deleted. This ensures data integrity and the ability to recover deleted data if necessary.

### Features

- **Tree-Structured Property Management**: Organizes properties in a hierarchical manner, allowing for complex relationships and shared resources.
- **RESTful API**: Interaction with the property data through a REST API.
- **Web Interface**: A basic web application for visualizing property data.

### Architecture Design

#### Overall Structure

The system follows a client-server architecture, where the backend (server) provides a RESTful API for managing the property tree, and the frontend (client) interacts with this API to present the data to the user. The backend and frontend applications run locally, while the database runs inside a Docker container.

#### Component Diagram

```
+----------------------+
|                      |
|  Frontend (React)    |
|   (Runs Locally)     |
|                      |
+---------+------------+
          |
          | API Requests
          |
          v
+---------+------------+          +-----------+-----------+
|                      |          |                       |
|  Backend (Symfony)   |          |    Database (MySQL)   |
|    RESTful API       | <------> |   (Docker Container)  |
|   (Runs Locally)     |          |                       |
|                      |          |                       |
+----------------------+          +-----------------------+

```

#### Explanation

- **Frontend (React)**: The web interface that users interact with, built using React. It runs locally and makes API requests to the backend.
- **Backend (Symfony)**: The server-side application built with Symfony, which runs locally and exposes a RESTful API for managing the property data.
- **Database (MySQL)**: The database where all property data is stored. It runs inside a Docker container.

#### Data Flow

1. **User Interaction**: The user interacts with the frontend through a web browser.
2. **API Requests**: The frontend sends API requests to the backend to fetch or modify property data.
3. **Business Logic**: The backend processes these requests, applying business logic and interacting with the database.
4. **Database Operations**: The backend performs the necessary CRUD operations on the database.
5. **API Responses**: The backend sends responses back to the frontend, which then updates the UI accordingly.

### Database Diagram

```plaintext
+--------------------+
|    property        |
+--------------------+
| id (PK)            |
| name               |
| type               |
| created            |
| modified           |
| status             |
+--------------------+
| INDEX: IDX_NAME    |
| INDEX: IDX_TYPE    |
| INDEX: IDX_STATUS  |
+--------------------+

      1
      |
      |
      N
+--------------------+
| property_relation  |
+--------------------+
| property_id (PK,FK)|
| parent_id (PK,FK)  |
| created            |
| modified           |
| status             |
+--------------------+
| INDEX: IDX_STATUS  |
+--------------------+
```

### SQL Definitions

#### property Table

```sql
CREATE TABLE property (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type TINYINT NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    status TINYINT DEFAULT 1 NOT NULL,
    INDEX IDX_NAME (name),
    INDEX IDX_TYPE (type),
    INDEX IDX_STATUS (status),
    PRIMARY KEY(id)
);
```

#### property_relation Table

```sql
CREATE TABLE property_relation (
    property_id INT NOT NULL,
    parent_id INT NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
    status TINYINT DEFAULT 1 NOT NULL,
    INDEX IDX_STATUS (status),
    PRIMARY KEY(property_id, parent_id)
);
```

### Requirements

- **PHP**: Version 8.3
- **Composer**: Version 2.7
- **Symfony**: Version 7
- **Node.js and NPM**: Version 20
- **Docker and Docker Compose**: Latest versions

### Installation

#### Set up the environment

- Ensure PHP, Composer, Symfony CLI, Node.js, and NPM are installed.
- Ensure Docker is installed and running.
- Build the Docker containers:

  ```sh
  docker-compose up --build
  ```

#### Clone the repository

```sh
git clone https://github.com/oravandres/bidrento.git
cd bidrento
```

#### Install API dependencies

```sh
composer install
```

#### Run the database migrations

```sh
php bin/console doctrine:migrations:migrate
```

#### Load database fixtures

```sh
php bin/console doctrine:fixtures:load
```

#### Start the server

```sh
symfony server:start -d
```

#### Install FE dependencies

```sh
cd frontend
npm install
```

#### Run FE application

```sh
npm start
```

**API is accessible** at `http://localhost:8000/api/properties`

**FE is accessible** at `http://localhost:3000`

### API Endpoints

#### Retrieve Full Property Tree (nested structure)

`GET /api/properties`

- **Example Request**

  ```sh
  curl -X GET "http://localhost:8000/api/properties"
  ```

- **Example Response**

  ```json
  [
    {
      "id": 1,
      "name": "Building complex",
      "type": "property",
      "created": "2024-05-21T21:00:59+00:00",
      "modified": "2024-05-21T21:00:59+00:00",
      "status": "active",
      "children": [
        {
          "id": 2,
          "name": "Building 1",
          "type": "property",
          "created": "2024-05-21T21:00:59+00:00",
          "modified": "2024-05-21T21:00:59+00:00",
          "status": "active",
          "children": [
            {
              "id": 5,
              "name": "Parking space 1",
              "type": "parking_space",
              "created": "2024-05-21T21:00:59+00:00",
              "modified": "2024-05-21T21:00:59+00:00",
              "status": "active",
              "children": []
            }
          ]
        },
        {
          "id": 3,
          "name": "Building 2",
          "type": "property",
          "created": "2024-05-21T21:00:59+00:00",
          "modified": "2024-05-21T21:00:59+00:00",
          "status": "active",
          "children": [
            {
              "id": 5,
              "name": "Parking space 1",
              "type": "parking_space",
              "created": "2024-05-21T21:00:59+00:00",
              "modified": "2024-05-21T21:00:59+00:00",
              "status": "active",
              "children": []
            },
            {
              "id": 6,
              "name": "Parking space 2",
              "type": "parking_space",
              "created": "2024-05-21T21:00:59+00:00",
              "modified": "2024-05-21T21:00:59+00:00",
              "status": "active",
              "children": []
            }
          ]
        },
        {
          "id": 4,
          "name": "Building 3",
          "type": "property",
          "created": "2024-05-21T21:00:59+00:00",
          "modified": "2024-05-21T21:00:59+00:00",
          "status": "active",
          "children": []
        }
      ]
    }
  ]
  ```

#### Retrieve Specific Property Details (flat structure)

`GET /api/properties/{id}`

- **Example Request**

  ```sh
  curl -X GET "http://localhost:8000/api/properties/2"
  ```

- **Example Response**

  ```json
  [
    {
      "property": "Building 1",
      "relation": null
    },
    {
      "property": "Building 2",
      "relation": "sibling"
    },
    {
      "property": "Building 3",
      "relation": "sibling"
    },
    {
      "property": "Building complex",
      "relation": "parent"
    },
    {
      "property": "Parking space 1",
      "relation": "child"
    }
  ]
  ```

#### Add New Property

`POST /api/properties`

- **Body**

  ```json
  {
    "name": "New Property",
    "type": "property"
  }
  ```

- **Example Request**

  ```sh
  curl -X POST "http://localhost:8000/api/properties" -H "Content-Type: application/json" -d '{"name": "New Property","type":"property"}'
  ```

- **Example Response**

  ```json
  {
    "id": 7,
    "name": "New Property",
    "type": 1,
    "created": "2024-05-21T21:18:53+00:00",
    "modified": "2024-05-21T21:18:53+00:00",
    "status": 1
  }
  ```

#### Add new property under an existing property in the tree

`POST /api/properties`

- **Body**

  ```json
  {
    "name": "Another Property",
    "type": "property",
    "parent_id": 1
  }
  ```

- **Example Request**

  ```sh
  curl -X POST "http://localhost:8000/api/properties" -H "Content-Type: application/json" -d '{"name": "Another Property","type":"property","parent_id": 1"}'
  ```

- **Example Response**

  ```json
  {
    "id": 8,
    "name": "Another Property",
    "type": 1,
    "created": "2024-05-21T21:20:41+00:00",
    "modified": "2024-05-21T21:20:41+00:00",
    "status": 1
  }
  ```

#### Remove

`DELETE /api/properties/7`

- **Example Request**

  ```sh
  curl -X DELETE "http://localhost:8000/api/properties/7" -H "Content-Type: application/json"
  ```

- **Example Response**

  ```json
  { "message": "Property and its relations soft deleted" }
  ```

### Testing

#### Create test database and run migrations

```sh
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
```

#### Running tests

```sh
php bin/phpunit
```
