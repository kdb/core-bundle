@api @group
Feature: admin
  In order to …
  As an api group
  I need to be able to …

  Background:
    Given the following users exist:
      | username | password | roles            |
      | admin    | admin    | ROLE_SUPER_ADMIN |
      | user     | user     | ROLE_USER        |

    And I add "Content-Type" header equal to "application/json"

  @createSchema
  Scenario: Get groups (anonymous)
    And I send a "GET" request to "/api/group"
    Then the response status code should be 401
    And the response should be in JSON
    And the JSON node "success" should be false

  Scenario: Get groups
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 0 elements

  Scenario: Create group with no title
    When I sign in with username "admin" and password "admin"
    And I send a "POST" request to "/api/group" with body:
      """
      {}
      """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "error" should not be null
    And the JSON node "error.message" should not be null

  Scenario: Create group (anonymous)
    And I send a "POST" request to "/api/group" with body:
      """
      {
        "title": "The first group"
      }
      """
    Then the response status code should be 401

  Scenario: Create group
    When I sign in with username "admin" and password "admin"
    And I send a "POST" request to "/api/group" with body:
      """
      {
        "title": "The first group"
      }
      """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "id" should be equal to 1
    And the JSON node "title" should be equal to "The first group"
    And the JSON node "groups" should not exist
    And the JSON node "api_data.permissions" should exist

  Scenario: Get groups
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 elements
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first group"

  Scenario: Get group
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should be equal to 1
    And the JSON node "title" should be equal to "The first group"
    And the JSON node "users" should have 0 elements
    And the JSON node "api_data.permissions" should exist

  Scenario: Update group
    When I sign in with username "admin" and password "admin"
    And I send a "PUT" request to "/api/group/1" with body:
      """
      {
        "title": "The first group (title updated)"
      }
      """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should be equal to 1
    And the JSON node "title" should be equal to "The first group (title updated)"
    And the JSON node "users" should not exist
    And the JSON node "api_data.permissions" should exist

  Scenario: Get group
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should be equal to 1
    And the JSON node "title" should be equal to "The first group (title updated)"
    And the JSON node "users" should have 0 elements
    And the JSON node "api_data.permissions" should exist

  Scenario: Get groups
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 elements
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first group (title updated)"

  Scenario: Delete group
    When I sign in with username "admin" and password "admin"
    And I send a "DELETE" request to "/api/group/1"
    Then the response status code should be 204

  Scenario: Get groups
    When I sign in with username "admin" and password "admin"
    And I send a "GET" request to "/api/group"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 0 elements

  Scenario: Cannot create duplicate group
    When I sign in with username "admin" and password "admin"
    And I send a "POST" request to "/api/group" with body:
      """
      {
        "title": "A unique group title"
      }
      """
    Then the response status code should be 201

    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/group" with body:
      """
      {
        "title": "A unique group title"
      }
      """
    Then the response status code should be 409

  Scenario: Delete group with content
    When I sign in with username "admin" and password "admin"
    When I send a "POST" request to "/api/slide" with body:
      """
      {
        "id": null,
        "title": "Slide in group",
        "media": [],
        "channels": [],
        "groups": [2]
      }
      """
    Then the response status code should be 200
    When I send a "POST" request to "/api/channel" with body:
      """
      {
        "id": null,
        "title": "Channel in group",
        "slides": [],
        "screens": [],
        "groups": [2],
        "orientation": "",
        "created_at": 1507280950,
        "sharing_indexes": [],
        "schedule_repeat_days": []
      }
      """
    Then the response status code should be 200
    When I send a "POST" request to "/api/screen" with body:
      """
      {
        "id": null,
        "title": "Screen in group",
        "description": "Description of The first screen",
        "groups": [2]
      }
      """
    Then the response status code should be 200
    And I send a "DELETE" request to "/api/group/2"
    Then the response status code should be 204
    And I send a "GET" request to "/api/group"
    Then the response status code should be 200
    And the JSON node "" should have 0 elements
    And I send a "GET" request to "/api/channel/1"
    Then the response status code should be 200
    And the JSON node "title" should be equal to "Channel in group"
    And I send a "GET" request to "/api/screen/1"
    Then the response status code should be 200
    And the JSON node "title" should be equal to "Screen in group"
    And I send a "GET" request to "/api/slide/1"
    Then the response status code should be 200
    And the JSON node "title" should be equal to "Slide in group"

  @dropSchema
  Scenario: Drop schema
