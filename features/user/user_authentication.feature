Feature: Autenticación de usuarios
  Como usuario del sistema
  Quiero autenticarme
  Para acceder a mis recursos protegidos

  Scenario: Buscar usuario por email existente
    Given Tengo un usuario existente con email "user@example.com"
    When Busco el usuario por email "user@example.com"
    Then Debo obtener los datos del usuario incluyendo su email

  Scenario: Buscar usuario por username existente
    Given Tengo un usuario existente con username "testuser"
    When Busco el usuario por username "testuser"
    Then Debo obtener los datos del usuario incluyendo su username