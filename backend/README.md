# Tipping API — Backend Documentation

This is the backend for the **Tipping Platform**, built with **Laravel 11** and **Laravel Sanctum**.  
It provides authentication, email verification, password reset, and tipping-related user management endpoints.

---

##  Base URL

```

[http://127.0.0.1:8000/api](http://127.0.0.1:8000/api)

```

All endpoints are prefixed with `/api`.

---

##  Authentication

- **Auth type:** Bearer token (Sanctum personal access tokens)  
- **Header:**  
```

Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json

```

---

##  Health Check

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

##  Authentication Endpoints

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

 **Note:** A **verification email** is automatically sent via **Mailtrap**. See [Mailtrap Setup](#-mailtrap-setup).

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

 **If the email is not verified**, login is blocked:

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

##  Email Verification with MailTrap

### Verification Link

The backend emails a verification link:

```
GET /email/verify/{id}/{hash}
```

#### Response

```json
{ "message": "Email verified successfully" }
```

 **Development Flow:**

* The email is sent to your **Mailtrap inbox**.
* Open Mailtrap → copy the verification link → paste in browser or hit with Postman.
* Once verified, the user can log in.

---

##  Password Reset Flow

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

 **Development Flow:**

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

##  Error Responses

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

##  Environment Variables (Backend)

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

##  Mailtrap Setup

The API is preconfigured to use **[Mailtrap](https://mailtrap.io/)** for all outgoing mail (verification and password reset).

* Create a free Mailtrap account.
* Copy the SMTP credentials into your `.env`.
* All verification and reset emails will appear in the Mailtrap inbox, safe for testing.
* In production, replace these with real SMTP credentials (SendGrid, Mailgun, Gmail, etc.).

---

##  Route Summary

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

##   Notes

* Save the token returned by **/login** or **/register**.
* Send it in the `Authorization: Bearer` header for protected requests.
* Always include `Accept: application/json` in headers.
* For password reset:

  * `/forgot-password` sends a reset link to Mailtrap.
  * The **frontend form** reads the token & email from the URL.
  * The form then calls `/reset-password`.

---

##  Example Postman Setup

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
## `balance` is included for later tipping features but should be **read-only** for users.




# Chapa Payment Integration

This backend module integrates the **Chapa payment gateway** to handle tipping creators. It includes payment initialization, webhook handling, tip status tracking, and automatic creator balance updates.

---

## Features

* Initialize tip payments via Chapa checkout.
* Receive and verify webhook notifications from Chapa.
* Update tip status (`pending`, `succeeded`, `failed`).
* Automatically update creator balances after successful payments.
* Secure HMAC-SHA256 verification for webhook requests.
* Frontend-friendly endpoints to track tip status.

---

## Environment Variables

Add the following to your `.env` file:

```env
# Chapa API Keys
CHAPA_PUBLIC_KEY=CHAPUBK_TEST-xxxx
CHAPA_SECRET_KEY=CHASECK_TEST-xxxx

# Webhook Secret
CHAPA_WEBHOOK_SECRET=YourSecretHere

# Webhook and return URLs
CHAPA_WEBHOOK_URL=https://<your-domain-or-ngrok>/api/chapa/webhook
CHAPA_RETURN_URL=https://<your-domain-or-ngrok>/payment-result
```

**Notes:**

* Use **ngrok** URLs for local development.
* `CHAPA_WEBHOOK_SECRET` must match the secret in your Chapa dashboard.
* Switch to production API keys in live deployments.

---

## API Endpoints

### 1. Create Tip & Initialize Payment

```
POST /api/creator/{id}/tips
```

**Request Body:**

```json
{
  "amount": 50,
  "message": "Great content!",
  "anonymous": false
}
```

**Response:**

```json
{
  "message": "Checkout initialized",
  "checkout_url": "https://checkout.chapa.co/checkout/payment/<id>",
  "tx_ref": "tip_XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "tip_id": 17
}
```

* `checkout_url` → redirect user to Chapa for payment.
* `tx_ref` → track tip status.

---

### 2. Check Tip Status

```
GET /api/tips/{tx_ref}/status
```

**Response:**

```json
{
  "tx_ref": "tip_XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "status": "pending|succeeded|failed",
  "amount": "50.00",
  "message": "Great content!"
}
```

* Frontend can poll this endpoint to update UI after payment.

---

### 3. Webhook Endpoint

```
POST /api/chapa/webhook
```

* Chapa sends POST requests after payments.
* Server verifies signature using `CHAPA_WEBHOOK_SECRET`.
* Updates tip status and creator balance upon successful payment.

**Important:**

* Only POST requests are allowed; GET requests return `405 Method Not Allowed`.
* Verify webhook payload contains `tx_ref`.
* Webhook endpoint should not be called manually by frontend.

---

### 4. Payment Result Page

```
GET /payment-result
```

**Response:**

```json
{
  "message": "Payment completed. Check tip status via /api/tips/{tx_ref}/status."
}
```

* Optional confirmation page for users after payment.

---

## Workflow

1. User initiates tip → POST `/api/creator/{id}/tips`.
2. Server returns `checkout_url` → frontend redirects to Chapa.
3. Payment completes → Chapa sends webhook POST `/api/chapa/webhook`.
4. Server verifies webhook → updates tip status → updates creator balance.
5. Frontend polls `/api/tips/{tx_ref}/status` → shows payment result.

---

## Notes

* **Sandbox Mode:** Use test keys for development; live keys for production.
* **Logs:** Check `storage/logs/laravel.log` for webhook and payment info.
* **Ngrok:** Use ngrok for local webhook testing. Ensure the URL matches `CHAPA_WEBHOOK_URL`.
* **Error Handling:**

  * `400 Bad Request` → usually missing `tx_ref` in webhook payload.
  * `405 Method Not Allowed` → GET request sent to webhook endpoint.
  * Signature mismatch → verify `CHAPA_WEBHOOK_SECRET`.


---

## Database

* `tips` table stores all tip transactions.
* `users` table stores creator balances.
* Tip status updates are transactional to avoid race conditions.



#  Payouts, Analytics & Role Management

This module introduces **payout handling for creators**, **analytics insights**, and **role-based access control (RBAC)** to separate **admins** from **creators**.

It also defines how **admins register** and explains the APIs available for both frontend and backend integration.

---

##  Overview

* **Creators** earn money through tips and can request payouts.
* **Admins** manage payout approvals, rejections, and payment confirmations.
* **Analytics** provides creators with insights into their tips and earnings.
* **Role management** ensures only authorized users can access specific features.

---

##  Authentication

* All endpoints require **Bearer Token authentication**.
* Token is issued upon login and must be sent in the `Authorization` header:

```
Authorization: Bearer <token>
```

---

##  Role Management

Every user has a `role` field:

* **creator** → Can request payouts, view analytics.
* **admin** → Can view/manage payouts.

Role enforcement is handled automatically by the backend.

---

##  Admin Registration

Admins are created manually for security.

* Only backend can create admins directly (via database or console command).
* Once created, an admin logs in just like a normal user but gains access to **admin APIs**.

---

##  API Endpoints

###  Creator Endpoints

| Method | Endpoint                 | Description                                      |
| ------ | ------------------------ | ------------------------------------------------ |
| `POST` | `/api/payouts`           | Request a payout (balance deducted immediately). |
| `GET`  | `/api/creator/analytics` | Fetch analytics about tips and balance.          |

**Example — Request Payout**

Request:

```json
POST /api/payouts
{
  "amount": 50,
  "note": "Weekly withdrawal"
}
```

Response:

```json
{
  "message": "Payout requested",
  "payout": {
    "id": 1,
    "amount": "50.00",
    "status": "pending",
    "reference": "payout_xxxx",
    "note": "Weekly withdrawal"
  }
}
```

**Example — Creator Analytics**

```json
GET /api/creator/analytics
```

Response:

```json
{
  "total_tips": 25,
  "total_amount": "350.00",
  "last_tip": "2025-09-05 14:20:00",
  "top_tipper": "Jane Doe",
  "balance": "120.00"
}
```

---

###  Admin Endpoints

| Method | Endpoint                      | Description                                   |
| ------ | ----------------------------- | --------------------------------------------- |
| `GET`  | `/api/payouts`                | View all payout requests.                     |
| `PUT`  | `/api/payouts/{id}/approve`   | Approve a payout (moves status → `approved`). |
| `PUT`  | `/api/payouts/{id}/reject`    | Reject payout & refund creator balance.       |
| `PUT`  | `/api/payouts/{id}/mark-paid` | Mark an approved payout as paid.              |

**Example — Approve Payout**

```json
PUT /api/payouts/1/approve
```

Response:

```json
{
  "message": "Payout approved",
  "payout": {
    "id": 1,
    "status": "approved",
    "processed_at": "2025-09-09 12:44:00"
  }
}
```

**Example — Reject Payout**

```json
PUT /api/payouts/1/reject
{
  "reason": "Invalid bank details"
}
```

Response:

```json
{
  "message": "Payout rejected + refunded",
  "payout": {
    "id": 1,
    "status": "rejected",
    "note": "Invalid bank details"
  }
}
```

---

##  Data Flow

1. **Creator workflow**

   * Earns balance through tips.
   * Requests a payout.
   * Balance is deducted immediately.
   * Status starts as `pending`.

2. **Admin workflow**

   * Reviews payout requests.
   * Can either:

     * Approve (status → `approved`)
     * Reject (status → `rejected`, funds refunded)
     * Mark as Paid (status → `paid`, after manual transfer)

3. **Analytics workflow**

   * Creator fetches analytics at `/api/creator/analytics`.
   * Returns tips count, earnings, last tip date, top supporter, and current balance.

---

##  Frontend Integration Guide

* Always attach `Authorization: Bearer <token>` header.
* **Creators** only see `/api/creator/analytics` and `/api/payouts (POST)`.
* **Admins** only see `/api/payouts (GET/PUT)` endpoints.
* Use returned statuses (`pending`, `approved`, `rejected`, `paid`) to style UI badges.
* For analytics, frontend can render:

  * Total earned amount → Earnings chart.
  * Last tip → "Recent activity" card.
  * Top tipper → Highlight top supporter.

---

## note

* **Creators** → Request payouts, view analytics.
* **Admins** → Manage payouts (approve, reject, mark paid).
* **RBAC** → Securely restricts access by role.
* **Admin accounts** are created manually, not via public registration.



