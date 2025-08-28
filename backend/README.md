
# Tipping API — Backend Documentation

This is the backend for the **Tipping Platform**, built with **Laravel 11** and **Laravel Sanctum**.  
It provides authentication, email verification, password reset, and tipping-related user management endpoints.

---

## 🔗 Base URL

```

[http://127.0.0.1:8000/api](http://127.0.0.1:8000/api)

```

All endpoints are prefixed with `/api`.

---

## ⚙️ Authentication

- **Auth type:** Bearer token (Sanctum personal access tokens)  
- **Header:**  
```

Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json

```

---

## 🩺 Health Check

### Endpoint
```

GET /health

````

### Response (200)
```json
{
  "status": "ok",
  "database": "connected"
}
````

---

## 👤 Authentication Endpoints

### 1) Register User

```
POST /register
```

#### Request Body

```json
{
  "name": "Ada Lovelace",
  "email": "ada@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "role": "tipper"
}
```

#### Response (201)

```json
{
  "message": "User registered successfully. Please verify your email.",
  "user": {
    "id": 1,
    "name": "Ada Lovelace",
    "email": "ada@example.com",
    "email_verified_at": null,
    "role": "tipper",
    "created_at": "2025-08-28T14:21:00Z"
  },
  "token": "1|2WJkTyhO..."
}
```

---

### 2) Login

```
POST /login
```

#### Request Body

```json
{
  "email": "ada@example.com",
  "password": "secret123"
}
```

#### Response (200)

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Ada Lovelace",
    "email": "ada@example.com",
    "email_verified_at": "2025-08-28T15:22:00Z",
    "role": "tipper"
  },
  "token": "1|xYzABC123..."
}
```

⚠️ **Note:** If the user has not verified their email, login returns:

```json
{ "message": "Please verify your email before logging in." }
```

---

### 3) Logout

```
POST /logout
Authorization: Bearer <token>
```

#### Response (200)

```json
{ "message": "Logged out successfully" }
```

---

## 📧 Email Verification

### Verification Link

The backend emails a link like:

```
GET /email/verify/{id}/{hash}
```

* Clicking it verifies the user and returns JSON:

```json
{ "message": "Email verified successfully" }
```

### Frontend Flow

* Show a “Verify your email” notice after registration.
* The email contains a **button** that links to the frontend (optional) or directly to this endpoint.

---

## 🔑 Password Reset Flow

### 1) Request Password Reset

```
POST /forgot-password
```

#### Request Body

```json
{ "email": "ada@example.com" }
```

#### Response (200)

```json
{ "message": "Password reset link sent to your email" }
```

* The backend generates a **reset link** and sends it by email.
* The link uses the **frontend URL** defined in `.env` (`FRONTEND_URL=http://localhost:3000`).
* Example reset link in the email:

```
http://localhost:3000/reset-password?token=XYZ123&email=ada@example.com
```

---

### 2) Reset Password

```
POST /reset-password
```

#### Request Body

```json
{
  "token": "XYZ123",
  "email": "ada@example.com",
  "password": "newSecret123",
  "password_confirmation": "newSecret123"
}
```

#### Response (200)

```json
{ "message": "Password reset successful" }
```

---

## 🔒 Error Responses

### 401 Unauthorized

```json
{ "message": "Unauthenticated." }
```

### 403 Forbidden (Unverified Email)

```json
{ "message": "Please verify your email before logging in." }
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field must be a valid email address."]
  }
}
```

---

## 🌍 Environment Variables (Backend)

```env
APP_NAME="Tipping API"
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tipping_api_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tippingapi.com"
MAIL_FROM_NAME="Tipping API"
```

---

## 📑 Route Summary

| Method | Path                        | Auth         | Purpose                                         |
| ------ | --------------------------- | ------------ | ----------------------------------------------- |
| GET    | `/health`                   | —            | Health check                                    |
| POST   | `/register`                 | —            | Register new user (sends verification email)    |
| POST   | `/login`                    | —            | Login and issue token (requires verified email) |
| POST   | `/logout`                   | Bearer token | Revoke token                                    |
| GET    | `/email/verify/{id}/{hash}` | Signed link  | Verify user email                               |
| POST   | `/forgot-password`          | —            | Send password reset link                        |
| POST   | `/reset-password`           | —            | Reset user password                             |

---

## 🛠️ Frontend Integration Notes

* Store the `token` returned by **/login** or **/register**.
* Send it in the `Authorization: Bearer` header for all protected requests.
* Always send `Accept: application/json` to avoid Laravel HTML error pages.
* For password reset:

  * `/forgot-password` sends the reset link.
  * The **frontend form** should capture `token` and `email` from the link.
  * The form should call `/reset-password` with the new password.

---

## ✅ Example Postman Setup

1. Create an environment in Postman:

   ```json
   {
     "baseUrl": "http://127.0.0.1:8000/api",
     "authToken": ""
   }
   ```

2. After login, save token to environment variable:

   * In Postman → **Tests tab** for `/login`:

     ```js
     let res = pm.response.json();
     if (res.token) {
       pm.environment.set("authToken", res.token);
     }
     ```

3. Use `{{authToken}}` in Authorization header for protected routes:

   ```
   Authorization: Bearer {{authToken}}
   ```

---

## 📌 Summary
It provides a complete **authentication module** for the Tipping Platform:

* User registration
* Email verification
* Login/logout
* Password reset (frontend-based reset flow)
* Health check

