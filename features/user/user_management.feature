Feature: Gestión de usuarios
  Como administrador del sistema
  Quiero gestionar usuarios
  Para mantener la seguridad y organización del sistema

  Scenario: Crear un nuevo usuario exitosamente
    Given Tengo una conexión a la base de datos de prueba
    When Creo un usuario con username "testuser", email "test@example.com" y password "secure123"
    Then Debo obtener un ID de usuario válido
    And El usuario con email "test@example.com" debe existir en la base de datos

  Scenario: Obtener un usuario por ID
    Given Tengo un usuario existente con username "existinguser"
    When Busco el usuario por su ID
    Then Debo obtener los datos del usuario incluyendo username "existinguser"

  Scenario: Actualizar un usuario
    Given Tengo un usuario existente con username "oldusername"
    When Actualizo el username a "newusername"
    Then El usuario debe tener el username "newusername" en la base de datos

  Scenario: Eliminar un usuario
    Given Tengo un usuario existente con username "tobedeleted"
    When Elimino el usuario
    Then El usuario no debe existir en la base de datos