# KaAyos API Reference

All API endpoints are protected by **Laravel Sanctum** and require a valid `Authorization: Bearer <token>` header.

Rate limits are enforced on OTP endpoints to prevent abuse.

---

## 1. Password Change — Send OTP

Sends a 6-digit OTP to the authenticated user's email after validating the current password.

**`POST /password-otp/send`**

### Request

```json
{
  "current_password": "current-pass"
}
```

### Success Response (200)

```json
{
  "message": "OTP sent to your email."
}
```

### Error Responses

| Status | Condition                       |
| ------ | ------------------------------- |
| 422    | `current_password` is incorrect |
| 429    | Rate limit exceeded (3/hour)    |

---

## 2. Password Change — Verify OTP & Update

Verifies the OTP and updates the password. Requires current password re-confirmation.

**`POST /password-otp/verify`**

### Request

```json
{
  "otp": "123456",
  "current_password": "current-pass",
  "new_password": "NewStr0ng!Pass",
  "new_password_confirmation": "NewStr0ng!Pass"
}
```

### Success Response (200)

```json
{
  "message": "Password changed successfully."
}
```

### Error Responses

| Status | Condition                       |
| ------ | ------------------------------- |
| 422    | Invalid/expired OTP, or incorrect current password |
| 422    | Validation errors (password too weak, mismatch)    |
| 429    | Rate limit exceeded (5/hour)    |

### Password Rules

- Minimum 8 characters
- Mixed case (uppercase + lowercase)
- At least one digit
- At least one symbol

---

## 3. Email Change — Send OTP

Sends a 6-digit OTP to the **new email** address. Validates current password and checks the 30-day cooldown.

**`POST /email-otp/send`**

### Request

```json
{
  "new_email": "new@example.com",
  "new_email_confirmation": "new@example.com",
  "current_password": "current-pass"
}
```

### Success Response (200)

```json
{
  "message": "A verification code has been sent to new@example.com."
}
```

### Error Responses

| Status | Condition                              |
| ------ | -------------------------------------- |
| 422    | Password incorrect                     |
| 422    | New email is same as current           |
| 422    | New email already taken                |
| 429    | 30-day email change cooldown           |
| 429    | Rate limit exceeded (3/hour)           |

---

## 4. Email Change — Verify OTP & Update

Verifies the OTP and finalizes the email update. Sends a notification to the old email.

**`POST /email-otp/verify`**

### Request

```json
{
  "otp": "123456"
}
```

### Success Response (200)

```json
{
  "message": "Email changed successfully."
}
```

### Error Responses

| Status | Condition                       |
| ------ | ------------------------------- |
| 422    | Invalid/expired OTP             |
| 422    | No pending email change found   |
| 429    | Rate limit exceeded (5/hour)    |

---

## 5. Update Profile

Updates the user's full name, phone number, and address (barangay).

**`PUT /api/profile`**

### Request

```json
{
  "fullName": "Maria Santos",
  "phone": "09171234567",
  "barangay": "Poblacion 1"
}
```

### Success Response (200)

```json
{
  "message": "Personal information saved.",
  "fullName": "Maria Santos",
  "email": "maria@example.com",
  "phone": "09171234567",
  "barangay": "Poblacion 1"
}
```

### Validation Rules

| Field     | Rule                                        |
| --------- | ------------------------------------------- |
| fullName  | Required, string, max 255                   |
| phone     | Nullable, must match `+63` or `09xx` format |
| barangay  | Nullable, string, max 255                   |

---

## 6. Update Preferences

Updates notification and language preferences.

**`PUT /api/preferences`**

### Request

```json
{
  "emailNotifications": "All updates",
  "language": "Filipino"
}
```

### `emailNotifications` allowed values

- `All updates`
- `Bookings only`
- `Messages only`
- `None`

### `language` allowed values

- `Filipino`
- `English`

### Success Response (200)

```json
{
  "message": "Preferences saved."
}
```

---

## 7. Upload Avatar

Uploads a profile avatar image.

**`POST /api/profile/avatar`**

### Request (multipart/form-data)

| Field  | Type | Rules                                    |
| ------ | ---- | ---------------------------------------- |
| avatar | File | Required, image, max 2MB, jpeg/png/jpg/gif/webp |

### Success Response (200)

```json
{
  "message": "Avatar uploaded.",
  "avatar_url": "/storage/avatars/abc123.jpg"
}
```

The previous avatar (if any) is automatically deleted.
