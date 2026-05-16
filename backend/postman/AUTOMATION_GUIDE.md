# EMS Postman Auto-Token System 🔐

## Overview
The EMS Postman collection now includes **automatic JWT token management**. No more manual copy/paste!

---

## ✨ What's New

### 1. **Collection-Level Authentication**
- All protected requests inherit Bearer {{token}} from collection auth
- Fallback to request-level auth headers for compatibility
- Defined in collection auth section

### 2. **Login Request - Test Script** ✅
Automatically stores JWT token after successful login:
```javascript
const response = pm.response.json();
if (response.success && response.data && response.data.token) {
    pm.environment.set("token", response.data.token);
    console.log("🔐 Login token stored");
}
```

### 3. **Register Request - Test Script** ✅
Automatically stores JWT token if returned during registration:
```javascript
// Same logic as Login - captures token for immediate use
```

### 4. **Protected Requests - Pre-Request Scripts** ✅
All protected endpoints (Logout, Refresh, Me) validate token before request:

**Pre-Request Script:**
```javascript
if (!pm.environment.get("token")) {
    console.warn("⚠️ No token found. Please login first.");
    pm.warn("🔴 TOKEN MISSING: Run Login request first");
}
```

### 5. **Protected Requests - Test Scripts** ✅
Refresh endpoint automatically updates token:
```javascript
// Captures new token and updates environment
pm.environment.set("token", response.data.token);
```

---

## 🚀 How to Use

### Step 1: Import Collection
1. Open Postman
2. Click **Import** 
3. Select `postman/EMS.postman_collection.json`
4. ✅ Imported

### Step 2: Import Environment
1. Click **Environments** (left sidebar)
2. Click **Import**
3. Select `postman/EMS_Local.postman_environment.json`
4. ✅ Environment loaded
5. Select **EMS Local** from dropdown

### Step 3: Register or Login
1. **Click Register** or **Login** request
2. **Click Send**
3. ✅ Token automatically stored in {{token}} variable
4. **Console** shows: "🔐 Token stored eyJhbGc..."

### Step 4: Use Protected Endpoints
1. **Click Logout**, **Refresh**, or **Me**
2. **Click Send**
3. ✅ Authorization header automatically includes token
4. **Console** validates token before each request

### Step 5: Refresh Token (Optional)
1. **Click Refresh** to get new token
2. ✅ Environment automatically updated with new token
3. Subsequent requests use new token

### Step 6: Logout
1. **Click Logout**
2. ✅ Token invalidated on server
3. Pre-next request will warn if token is missing

---

## 📋 Automation Features

### Request-Level Automation

| Request | Script | Trigger | Action |
|---------|--------|---------|--------|
| **Register** | Test | On Success | Store token in {{token}} |
| **Login** | Test | On Success | Store token in {{token}} |
| **Logout** | Pre-Request | Before Request | Validate token exists |
| **Logout** | Test | On Success | Confirm logout |
| **Refresh** | Pre-Request | Before Request | Validate token exists |
| **Refresh** | Test | On Success | Update token in {{token}} |
| **Me** | Pre-Request | Before Request | Validate token exists |

### Console Logging
All scripts log to Postman console:
- ✅ "Token stored" - Token captured
- 🔐 "Login token stored" - Login successful
- 🔄 "Token refreshed" - Token rotated
- 🔓 "Logout successful" - Token invalidated
- ⚠️ "No token found" - Missing authentication
- 🔴 "TOKEN MISSING" - Warning notification

---

## 🔍 Environment Variables

### Available Variables
```json
{
  "base_url": "http://127.0.0.1:8000",
  "token": ""
}
```

### Auto-Populated Variables
- `{{base_url}}` - API base URL (manual)
- `{{token}}` - JWT token (auto-populated after login/register/refresh)

### Access in Scripts
```javascript
// Get variable
const token = pm.environment.get("token");

// Set variable
pm.environment.set("token", "new-value");

// Check if variable exists
if (!pm.environment.get("token")) {
    // Token missing
}
```

---

## 🧪 Test Workflow

### Workflow 1: Register & Protected Endpoints
```
1. Register → Token stored in {{token}}
2. Me → Uses {{token}} automatically
3. Refresh → Token updated automatically
4. Logout → Uses new token + clears
5. Me (after logout) → 401 Unauthorized ✅
```

### Workflow 2: Login & Protected Endpoints
```
1. Login → Token stored in {{token}}
2. Me → Uses {{token}} automatically
3. Logout → Uses {{token}} + clears
4. Me (after logout) → 401 Unauthorized ✅
```

---

## 🐛 Debugging

### View Console Output
1. Click **Console** (bottom of Postman)
2. Send any request
3. View script execution logs
4. Errors appear in red

### Verify Token Storage
1. Click **Environments** (left sidebar)
2. Select **EMS Local**
3. View `token` value - should be JWT string
4. Click value to see full token

### Check Authorization Header
1. Open request (Logout, Refresh, Me)
2. Click **Headers** tab
3. Verify `Authorization: Bearer {{token}}`
4. Click **Console** to see token value used

### Common Issues

| Issue | Solution |
|-------|----------|
| "TOKEN MISSING" warning | Run Login/Register first |
| 401 Unauthorized on Me | Login/Register to get token |
| Token not updating on Refresh | Check response has `data.token` |
| Headers show Bearer null | Confirm environment is selected |

---

## 📝 Notes

- ✅ Collection supports multiple environments (Local, Staging, Production)
- ✅ Token persists in environment until Logout or explicitly cleared
- ✅ All scripts include console logging for debugging
- ✅ Pre-request validation prevents requests without token
- ✅ Postman Bearer Auth (collection level) ensures consistency
- ✅ Scripts are non-destructive - run requests in any order

---

## 🔒 Security Notes

- ⚠️ Tokens stored in Postman environment (local only)
- ⚠️ Do NOT share environment files containing real tokens
- ⚠️ Use only in development/testing environments
- ⚠️ Refresh token regularly via Refresh endpoint
- ⚠️ Clear token and logout before sharing session

---

**Version:** 1.0  
**Last Updated:** 2026-03-22  
**Status:** ✅ Active

Postman Auto-Token System Enabled Successfully ✅
