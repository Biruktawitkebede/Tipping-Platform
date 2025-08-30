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

✅ **Note:** A **verification email** is automatically sent via **Mailtrap**. See [Mailtrap Setup](#-mailtrap-setup).

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

⚠️ **If the email is not verified**, login is blocked:

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

## 📧 Email Verification with MailTrap

### Verification Link

The backend emails a verification link:

```
GET /email/verify/{id}/{hash}
```

#### Response

```json
{ "message": "Email verified successfully" }
```

✅ **Development Flow:**

* The email is sent to your **Mailtrap inbox**.
* Open Mailtrap → copy the verification link → paste in browser or hit with Postman.
* Once verified, the user can log in.

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

✅ **Development Flow:**

* The reset email will appear in your **Mailtrap inbox**.
* The link is customized to point to your frontend:

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

# Mailtrap config
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tippingapi.com"
MAIL_FROM_NAME="Tipping API"
```

---

## 📬 Mailtrap Setup

The API is preconfigured to use **[Mailtrap](https://mailtrap.io/)** for all outgoing mail (verification and password reset).

* Create a free Mailtrap account.
* Copy the SMTP credentials into your `.env`.
* All verification and reset emails will appear in the Mailtrap inbox, safe for testing.
* In production, replace these with real SMTP credentials (SendGrid, Mailgun, Gmail, etc.).

---

## 📑 Route Summary

| Method | Path                        | Auth         | Purpose                                              |
| ------ | --------------------------- | ------------ | ---------------------------------------------------- |
| GET    | `/health`                   | —            | Health check                                         |
| POST   | `/register`                 | —            | Register new user + send Mailtrap verification email |
| POST   | `/login`                    | —            | Login & issue token (requires verified email)        |
| POST   | `/logout`                   | Bearer token | Revoke token                                         |
| GET    | `/email/verify/{id}/{hash}` | Signed link  | Verify user email (link from Mailtrap)               |
| POST   | `/forgot-password`          | —            | Send reset link (delivered via Mailtrap)             |
| POST   | `/reset-password`           | —            | Reset user password                                  |

---

## 🛠️ Frontend Integration Notes

* Save the token returned by **/login** or **/register**.
* Send it in the `Authorization: Bearer` header for protected requests.
* Always include `Accept: application/json` in headers.
* For password reset:

  * `/forgot-password` sends a reset link to Mailtrap.
  * The **frontend form** reads the token & email from the URL.
  * The form then calls `/reset-password`.

---

## ✅ Example Postman Setup

1. **Environment Variables**:

   ```json
   {
     "baseUrl": "http://127.0.0.1:8000/api",
     "authToken": ""
   }
   ```

2. **Save token after login**
   In the **Tests tab** for `/login`:

   ```js
   let res = pm.response.json();
   if (res.token) {
     pm.environment.set("authToken", res.token);
   }
   ```

3. **Use token** in protected requests:

   ```
   Authorization: Bearer {{authToken}}
   ```

---


##  User Profile Management 

This module enables authenticated users to **view and update their profile**, including uploading an avatar(file or url).
All routes are **protected with Sanctum** and require a valid Bearer token.

---

##  Database Schema (Users Table – Relevant Fields)

| Column      | Type              | Description                                  |
| ----------- | ----------------- | -------------------------------------------- |
| id          | bigint (PK)       | Unique user ID                               |
| name        | string            | User’s full name                             |
| email       | string (unique)   | User’s email address                         |
| role        | enum              | One of: `tipper`, `creator`, `admin`         |
| bio         | text (nullable)   | Short biography                              |
| avatar      | string (nullable) | File path to avatar (e.g. `avatars/xyz.png`) |
| balance     | decimal(12,2)     | Current account balance (for tipping system) |
| created\_at | timestamp         | User creation date                           |
| updated\_at | timestamp         | Last profile update                          |

⚡  Always use `avatar_url` from API response (never build it manually).

---

##  Endpoints

###  Get Current User Profile

**Request:**

```
GET /api/user
```

**Headers:**

```http
Authorization: Bearer {token}
Accept: application/json
```

**Response Example:**

```json
{
  "data": {
    "id": 7,
    "name": "Anteneh",
    "email": "anteneh8@gmail.com",
    "role": "tipper",
    "bio": "I am a new user",
    "avatar": "avatars/avatar123.png",
    "avatar_url": "http://127.0.0.1:8000/storage/avatars/avatar123.png",
    "balance": "0.00",
    "created_at": "2025-08-28T17:13:21.000000Z",
    "updated_at": "2025-08-28T17:32:28.000000Z"
  }
}
```

---

###  Update Profile

**Request:**

```
PUT /api/user
```

**Headers:**

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Body (form-data):**

| Field  | Type   | Required | Description                                |
| ------ | ------ | -------- | ------------------------------------------ |
| name   | string | optional | Update the user’s name                     |
| bio    | string | optional | Biography (max 1000 chars)                 |
| email  | string | optional | Must be valid and unique                   |
| avatar | file   | optional | Image file (jpg, jpeg, png, webp), max 2MB |

**Example (form-data):**

* `name: Jane Smith`
* `bio: I love Laravel`
* `avatar: [choose file]`

**cURL Example:**

```bash
curl -X PUT http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "name=Jane Smith" \
  -F "bio=I love Laravel" \
  -F "avatar=@/path/to/avatar.png"
```

**Response Example:**

```json
{
  "message": "Profile updated successfully.",
  "data": {
    "id": 7,
    "name": "Jane Smith",
    "email": "anteneh8@gmail.com",
    "role": "tipper",
    "bio": "I love Laravel",
    "avatar": "avatars/new_avatar.png",
    "avatar_url": "http://127.0.0.1:8000/storage/avatars/new_avatar.png",
    "balance": "0.00",
    "created_at": "2025-08-28T17:13:21.000000Z",
    "updated_at": "2025-08-29T11:00:00.000000Z"
  }
}
```

---

##  Validation Rules

* `name`: max 100 characters
* `bio`: max 1000 characters
* `email`: must be valid + unique
* `avatar`: must be an image (`jpg,jpeg,png,webp`) and max 2MB

---

##  Error Responses

* **Unauthenticated (no/invalid token):**

```json
{
  "message": "Unauthenticated."
}
```

* **Validation Error:**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

##  Notes

* Always send `Authorization: Bearer {token}` with requests.
* For avatar upload, use **`multipart/form-data`**, not JSON.
* Display avatars using `avatar_url` from API (handles both missing and uploaded avatars).
* `balance` is included for later tipping features but should be **read-only** for users.

