# AccessToken Model Analysis Report

## 1. Token Generation

- **Automatic Creation:**
  - Any model that includes the `AccessTokenable` concern ([app/models/concerns/access_tokenable.rb#L1-L18](app/models/concerns/access_tokenable.rb#L1-L18)) will automatically generate an access token after creation:
    - Association: [app/models/concerns/access_tokenable.rb#L4]
      ```ruby
      has_one :access_token, as: :owner, dependent: :destroy_async
      ```
    - Callback: [app/models/concerns/access_tokenable.rb#L5]
      ```ruby
      after_create :create_access_token
      ```
    - Method: [app/models/concerns/access_tokenable.rb#L8]
      ```ruby
      def create_access_token
        AccessToken.create!(owner: self)
      end
      ```

- **Included Models:**
  - Models like `User`, `AgentBot`, and `PlatformApp` include this concern ([app/models/user.rb#L49], [app/models/agent_bot.rb#L21], [app/models/platform_app.rb#L11]), so they automatically get an access token.

## 2. Token Access & Authentication

- **Controller Concern:**
  - The `AccessTokenAuthHelper` module ([app/controllers/concerns/access_token_auth_helper.rb#L1-L40]) is included in API controllers ([app/controllers/api/base_controller.rb#L2]).
    - It fetches the token from request headers and finds the corresponding `AccessToken`:
      [app/controllers/concerns/access_token_auth_helper.rb#L10]
      ```ruby
      @access_token = AccessToken.find_by(token: token) if token.present?
      ```
    - It sets the current resource (`@resource = @access_token.owner`) and, if appropriate, sets `Current.user`.

- **Authentication Flow:**
  - `authenticate_access_token!` ([app/controllers/concerns/access_token_auth_helper.rb#L13-L21]) ensures the token is valid and sets the resource context.
  - Bot access is further validated with `validate_bot_access_token!` ([app/controllers/concerns/access_token_auth_helper.rb#L27-L34]).

## 3. Usage in Controllers

- Controllers use the concern to authenticate API requests and set the current user or bot context.
- Example: [app/controllers/platform_controller.rb#L19]
  ```ruby
  @access_token = AccessToken.find_by(token: token) if token.present?
  ```
- The `SuperAdmin::AccessTokensController` ([app/controllers/super_admin/access_tokens_controller.rb#L1]) manages token lifecycle (creation, revocation, listing).

## 4. Summary Table

| Step                | Code Reference                                                                 | Description                                      |
|---------------------|-------------------------------------------------------------------------------|--------------------------------------------------|
| Generation          | [access_tokenable.rb#L4-L8](app/models/concerns/access_tokenable.rb#L4-L8)    | Token auto-created for owner after creation       |
| Association         | [access_token.rb#L22](app/models/access_token.rb#L22)                         | Polymorphic link to owner                        |
| Access/Lookup       | [access_token_auth_helper.rb#L10](app/controllers/concerns/access_token_auth_helper.rb#L10) | Token fetched from headers and DB                |
| Authentication      | [access_token_auth_helper.rb#L13-L21](app/controllers/concerns/access_token_auth_helper.rb#L13-L21) | Validates and sets resource context              |
| Controller Usage    | [platform_controller.rb#L19](app/controllers/platform_controller.rb#L19)      | Used in API controllers for authentication        |
| Management Endpoints| [super_admin/access_tokens_controller.rb#L1](app/controllers/super_admin/access_tokens_controller.rb#L1) | Token CRUD operations                            |

---

**Conclusion:**
Access tokens are automatically generated for key entities, stored with a secure association, and accessed via controller concerns for authentication. All references above are direct from the project code—no guesses.
