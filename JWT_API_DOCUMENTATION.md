# JWT Authentication API Documentation

## Overview
This Laravel application now includes comprehensive JWT (JSON Web Token) authentication. The JWT system allows secure API authentication using tokens.

## Features
- ✅ User Registration
- ✅ User Login
- ✅ Token Refresh
- ✅ User Logout
- ✅ Protected Routes
- ✅ User Profile Management
- ✅ Token Validation Middleware

## API Endpoints

### Authentication Endpoints

#### 1. User Registration
```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "User",
            "status": "Aktif"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 7200
    }
}
```

#### 2. User Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "User",
            "status": "Aktif"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 7200
    }
}
```

#### 3. Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "User",
            "status": "Aktif"
        }
    }
}
```

#### 4. Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 7200
    }
}
```

#### 5. Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

### Protected API Endpoints

All endpoints below require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer {your_jwt_token}
```

#### 6. Get User Data
```http
GET /api/data
Authorization: Bearer {token}
```

#### 7. Update User Data
```http
POST /api/data/{parameter}
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "Updated Name",
    "email": "updated@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### 8. Get Program Data
```http
GET /api/program
Authorization: Bearer {token}
```

#### 9. Get Unit Data
```http
GET /api/unit
Authorization: Bearer {token}
```

#### 10. Get Bill Data
```http
GET /api/bill
Authorization: Bearer {token}
```

#### 11. Store Bill Data
```http
POST /api/bill
Authorization: Bearer {token}
```

#### 12. Get Level Data
```http
GET /api/level
Authorization: Bearer {token}
```

#### 13. Update Level Data
```http
POST /api/level
Authorization: Bearer {token}
```

#### 14. Get Tagihan Data
```http
GET /api/tagihan
Authorization: Bearer {token}
```

#### 15. Get Price Data
```http
GET /api/price/{kelas}/{product}
Authorization: Bearer {token}
```

#### 16. Get Jadwal Data
```http
GET /api/jadwal
Authorization: Bearer {token}
```

#### 17. Update Jadwal Data
```http
POST /api/jadwal
Authorization: Bearer {token}
```

#### 18. Get Report Data
```http
GET /api/report
Authorization: Bearer {token}
```

#### 19. Update Report Data
```http
POST /api/report
Authorization: Bearer {token}
```

## Error Responses

### Authentication Errors

#### Invalid Credentials (401)
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

#### Token Expired (401)
```json
{
    "error": "Token expired"
}
```

#### Token Invalid (401)
```json
{
    "error": "Token invalid"
}
```

#### Token Not Found (401)
```json
{
    "error": "Token not found"
}
```

#### User Not Found (404)
```json
{
    "error": "User not found"
}
```

### Validation Errors (422)
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## Configuration

### JWT Configuration
The JWT configuration is located in `config/jwt.php`. Key settings:

- **TTL**: Token time to live (default: 120 minutes)
- **Refresh TTL**: Refresh token time to live (default: 20160 minutes / 2 weeks)
- **Algorithm**: HS256 (default)
- **Blacklist**: Enabled for token invalidation

### Environment Variables
Make sure these are set in your `.env` file:

```env
JWT_SECRET=your_jwt_secret_key_here
JWT_TTL=120
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
```

## Usage Examples

### Using cURL

#### 1. Register a new user:
```bash
curl -X POST http://your-domain.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### 2. Login:
```bash
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### 3. Access protected route:
```bash
curl -X GET http://your-domain.com/api/data \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### Using JavaScript/Fetch

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  if (data.success) {
    localStorage.setItem('token', data.data.token);
  }
  return data;
};

// Access protected route
const getData = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('/api/data', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return await response.json();
};
```

## Security Features

1. **Token Expiration**: Tokens expire after 2 hours by default
2. **Refresh Tokens**: Users can refresh tokens without re-authentication
3. **Token Blacklisting**: Logout invalidates tokens immediately
4. **Password Hashing**: Passwords are securely hashed using Laravel's Hash facade
5. **Input Validation**: All inputs are validated before processing
6. **CORS Support**: Configure CORS for cross-origin requests

## User Roles

The system supports different user roles:
- **Admin** (role: 0)
- **User** (role: 2) 
- **Guru** (role: 3)

## Token Structure

JWT tokens contain the following claims:
- `iss`: Issuer
- `iat`: Issued at
- `exp`: Expiration time
- `nbf`: Not before
- `sub`: Subject (user ID)
- `jti`: JWT ID

## Troubleshooting

### Common Issues

1. **Token Expired**: Use the refresh endpoint to get a new token
2. **Invalid Token**: Ensure the token is correctly formatted and not corrupted
3. **User Not Found**: The user may have been deleted or the token is invalid
4. **CORS Issues**: Configure CORS middleware for cross-origin requests

### Testing JWT

You can test JWT tokens using online tools like:
- JWT.io
- Postman
- Insomnia

## Next Steps

1. Configure CORS for your frontend application
2. Set up proper error handling in your frontend
3. Implement token refresh logic
4. Add rate limiting for authentication endpoints
5. Set up proper logging for security monitoring

