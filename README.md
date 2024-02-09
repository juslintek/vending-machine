# Project Setup and Running Guide

## Using DDEV (Recommended)

### Installation

1. Install DDEV by following the official guide: https://ddev.readthedocs.io/en/stable/

### Running the Project

- Start DDEV: `ddev start`
- Run command: `ddev php bin/console app:purchase-item`

## Running Standalone with PHP

If you prefer not to install DDEV,
you can run the project with PHP's built-in server or just some Docker image, for example:

```
docker run -it --rm -v $(pwd):/app -w /app php:cli php bin/console app:purchase-item
```

## Running Tests

- Start DDEV: `ddev start`
- Run command: `ddev php bin/phpunit`

## Running Tests Standalone with PHP

- Run command:

```
docker run -it --rm -v $(pwd):/app -w /app php:cli php bin/phpunit
```

# Comments about a task

### Correctness

The algorithms, including the change calculation method, follow logical steps to ensure that the correct amount of
change is returned and that coin reserve is updated accordingly. Implementing sorting of denominations in
descending order before calculating change ensures that the largest possible denominations are used first, which is
efficient and meets user expectations.

See: `calculateChange` [VendingMachine.php](src%2FModel%2FVendingMachine.php) class for the change calculation algorithm.

### Efficiency

The change calculation algorithm is efficient for the scale of a vending machine operation. It minimizes the number of
coins returned, although it does so through a linear search, which is acceptable given the small dataset (coin
denominations).

See: `calculateChange` method in [VendingMachine.php](src%2FModel%2FVendingMachine.php)

### Class Structure

The separation of concerns is applied in the class structure, with distinct responsibilities for the
VendingMachineRepository, service classes, and command classes. This promotes reusability and simplifies maintenance.

See:
- [VendingMachineRepository.php](src%2FRepository%2FVendingMachineRepository.php)
- [services.yaml](config%2Fservices.yaml)

### Method Structure

Methods are well-defined, with clear purposes. The use of dependency injection for services and
repositories in command classes follows best practices, enhancing testability and flexibility.

### Data Modelling

The data model captures the essential aspects of a vending machine change task, cash reserve management and tracking.
However, it's designed primarily for in-memory operation without direct database
interaction, which is suitable for the scope of this project but might need reevaluation for scalability or persistence
needs.

### Appropriateness of Data Types and Structures Used

Data types and structures (e.g., arrays for inventory and cash reserves) are appropriately chosen for the scale and
complexity of the application. Using associative arrays for mapping item names to prices and coin denominations to
quantities is straightforward and effective for the required operations.

### Clarity

The code is generally clear and readable, with meaningful variable names and concise method implementations. Comments
and method descriptions could enhance understanding, especially for more complex logic.

### Maintainability

The application's structure, with clear separation of concerns and encapsulation of business logic, supports
maintainability. Adherence to Symfony's conventions also aids in ensuring that other developers familiar with the
framework can understand and extend the code.

### Trade-off between Quality and Quantity of Code Delivered

The focus has been on delivering quality code that implements the required functionality robustly and efficiently,
without an unnecessary complexity. This approach sometimes requires more upfront development time to ensure code quality
but pays off in long-term maintainability and scalability.

### Appropriateness of Assumptions

Assumptions made (e.g., the scale of inventory and transactions, the absence of persistent storage) are reasonable for a
prototype or small-scale application. For a production system, especially one needing scalability or data persistence,
these assumptions would need reevaluation.

### Flexibility of design

- The design is flexible, with easily replaceable components thanks to the use of interfaces and dependency injection.
  This makes it straightforward to extend the application, for example, by adding new commands, integrating with a
  database, or changing the business logic.
- The design supports adding new features (e.g., dynamic inventory updates) or changing underlying implementations (
  e.g.,
  switching to database storage) with minimal impact on existing code.

### Improvements to be made

- The use of a more robust data storage solution (e.g., a database) would be beneficial for scalability and persistence.
- The addition of more comprehensive error handling and validation would enhance the application's robustness.
- The introduction of logging and monitoring would be valuable for tracking transactions and diagnosing issues.
- The addition of more comprehensive tests, including edge cases and error scenarios, would enhance the application's
  reliability.
- The introduction of a more comprehensive user interface (e.g., a web interface) would make the application more
  accessible and user-friendly.
- The addition of more detailed documentation and comments would enhance the code's readability and maintainability.
- Moving calculateChange method to a separate service class would make the VendingMachine class more focused on
  managing coin reserve and transactions.
- Moving `parseCoinInput` and `formatChangeOutput` method to a separate service class would make PurchaseItemCommand
  class more focused on input/output exchange. While parser and formatter would be reusable in other commands or
  classes, currently its implementation is tightly coupled to the command.
- The use of a more robust data structure (e.g., a tree or graph) for representing inventory and cash reserves would
  enhance the application's scalability and performance.

