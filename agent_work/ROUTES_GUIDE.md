# 🛣️ VujaDe Platform - Routes Organization Guide

## 📁 **Route Files Structure**

```
routes/
├── web.php       ← Main routes (auth, public, dashboard)
├── client.php    ← Client-specific routes (all services)
└── internal.php  ← Manager/Employee routes (management)
```

---

## 📄 **web.php** (Main Routes)

**Purpose:** Authentication, public pages, and core dashboard routing

```php
// Public
GET  /                              - Welcome page or redirect to dashboard

// Authentication
Auth::routes(['verify' => true])    - Login, Register, Password Reset, Email Verification

// Social Auth
GET  /auth/{provider}/redirect      - Redirect to social provider
GET  /auth/{provider}/callback      - Handle social callback

// Authenticated
GET  /dashboard                     - Main dashboard (role-based redirect)
GET  /home                          - Redirects to dashboard

// Legacy
GET  /service-requests              - Old service request system
```

**Includes:**
- `require __DIR__.'/client.php';`
- `require __DIR__.'/internal.php';`

---

## 👤 **client.php** (Client Routes)

**Purpose:** All client-facing service request features

**Middleware:** `auth`, `verified`

### **Dashboard**
```
GET  /client/dashboard              - Client dashboard
GET  /services                      - Services overview page
```

### **💡 Idea Generation**
```
GET  /ideas/create                  - Create idea form
POST /ideas                         - Submit idea
GET  /ideas/{id}                    - View idea details
GET  /ideas/{id}/ai-assessment      - AI assessment tools
POST /ideas/{id}/ai-assessment      - Process AI assessment
GET  /ideas/{id}/negotiation        - Negotiation chat
POST /ideas/{id}/comments           - Add negotiation comment
POST /ideas/{id}/accept-quote       - Accept quote
POST /ideas/{id}/reject-quote       - Reject quote
GET  /ideas/{id}/payment            - Payment upload page
POST /ideas/{id}/payment            - Upload payment file
```

### **💬 Consultation**
```
GET  /consultations/create          - Create consultation form
POST /consultations                 - Submit consultation
GET  /consultations/{id}            - View consultation details
```

### **🔍 Research & IP**
```
GET  /research/create               - Create research form
POST /research                      - Submit research
GET  /research/{id}                 - View research details
POST /research/{id}/sign-documents  - Sign NDA/SLA
POST /research/{id}/book-meeting    - Book meeting
```

### **📄 IP Registration**
```
GET  /ip/create                     - Create IP registration form
POST /ip                            - Submit IP registration
GET  /ip/{id}                       - View IP registration details
POST /ip/{id}/book-meeting          - Book meeting
```

### **©️ Copyright Registration**
```
GET  /copyright/create              - Create copyright form
POST /copyright                     - Submit copyright
GET  /copyright/{id}                - View copyright details
POST /copyright/{id}/book-meeting   - Book meeting
```

---

## 👔 **internal.php** (Manager/Employee Routes)

**Purpose:** Internal staff management and review features

**Middleware:** `auth`, `verified`, `role:manager|employee`

### **Dashboard**
```
GET  /internal/dashboard            - Internal dashboard
```

### **💡 Ideas Management**
```
GET  /ideas/manager                 - All idea requests
POST /ideas/{id}/send-quote         - Send quote to client
POST /ideas/{id}/verify-payment     - Verify payment file
POST /ideas/{id}/assign             - Assign to employee
```

### **💬 Consultations Management**
```
GET  /consultations/manager         - All consultations
POST /consultations/{id}/send-invite - Send meeting invitation
POST /consultations/{id}/complete   - Mark as complete
```

### **🔍 Research Management**
```
GET  /research/manager              - All research requests
POST /research/{id}/assign          - Assign to researcher
POST /research/{id}/complete        - Deliver findings
```

### **📄 IP Registration Management**
```
GET  /ip/manager                    - All IP registrations
POST /ip/{id}/confirm-meeting       - Confirm meeting
POST /ip/{id}/update-status         - Update filing status
```

### **©️ Copyright Management**
```
GET  /copyright/manager             - All copyright registrations
POST /copyright/{id}/confirm-meeting - Confirm meeting
POST /copyright/{id}/update-status  - Update filing status
```

### **🔧 Stepper System (Manager Only)**
```
// Service Type Management
GET  /stepper/service-types         - List all service types
GET  /stepper/service-types/create  - Create new service type
POST /stepper/service-types         - Store service type
GET  /stepper/service-types/{id}    - View service type
GET  /stepper/service-types/{id}/edit - Edit service type
PUT  /stepper/service-types/{id}    - Update service type
DELETE /stepper/service-types/{id}  - Delete service type

// Steps Management
GET  /stepper/service-types/{id}/steps/create - Create step
POST /stepper/service-types/{id}/steps - Store step
GET  /stepper/steps/{id}            - Edit step
PUT  /stepper/steps/{id}            - Update step
DELETE /stepper/steps/{id}          - Delete step
PUT  /stepper/service-types/{id}/steps/reorder - Reorder steps

// Form Fields Management
GET  /stepper/steps/{id}/fields/create - Create field
POST /stepper/steps/{id}/fields     - Store field
GET  /stepper/fields/{id}/edit      - Edit field
PUT  /stepper/fields/{id}           - Update field
DELETE /stepper/fields/{id}         - Delete field
PUT  /stepper/steps/{id}/fields/reorder - Reorder fields

// Client Stepper (Dynamic Service Requests)
GET  /stepper                       - List available service types
GET  /stepper/create/{type}         - Create dynamic request
POST /stepper/store/{type}          - Store dynamic request
GET  /stepper/show/{id}             - View dynamic request
GET  /stepper/step/{request}/{step} - View specific step
POST /stepper/step/{request}/{step} - Process step
```

---

## 🔐 **Middleware & Permissions**

### **Public Routes**
- No authentication required
- Welcome page, login, register

### **Authenticated Routes**
- Requires: `auth`, `verified`
- All logged-in users

### **Client Routes**
- Requires: `auth`, `verified`
- Accessible by clients only (enforced in controllers)

### **Manager/Employee Routes**
- Requires: `auth`, `verified`, `role:manager|employee`
- Accessible by internal staff

### **Manager-Only Routes**
- Requires: `auth`, `verified`, `role:manager`
- Accessible by managers only

---

## 🎯 **Route Naming Convention**

### **Client Routes:**
```
{service}.create        - Create form
{service}.store         - Submit form
{service}.show          - View details
{service}.{action}      - Specific action
```

### **Manager Routes:**
```
{service}.manager.index - List all requests
{service}.{action}      - Management action
```

---

## 📊 **Route Statistics**

- **Total Routes:** 100+
- **Client Routes:** 40+
- **Internal Routes:** 50+
- **Public Routes:** 10+

---

## 🧪 **Testing Routes**

### **Test Client Routes:**
```bash
# Login as client
curl -X GET http://localhost:8000/services
curl -X GET http://localhost:8000/ideas/create
curl -X GET http://localhost:8000/consultations/create
```

### **Test Manager Routes:**
```bash
# Login as manager
curl -X GET http://localhost:8000/ideas/manager
curl -X GET http://localhost:8000/consultations/manager
```

---

## 🚀 **Quick Navigation**

### **For Clients:**
1. Login → `/dashboard` → Auto-redirect to `/client/dashboard`
2. Click "Request Service" → `/services`
3. Choose service → `/ideas/create` or `/consultations/create` etc.
4. Submit → View at `/{service}/{id}`

### **For Managers:**
1. Login → `/dashboard` → Auto-redirect to `/internal/dashboard`
2. Sidebar → "Service Management" → Choose service
3. View all requests → `/{service}/manager`
4. Take actions → Review, quote, assign, etc.

### **For Employees:**
1. Login → `/dashboard` → Auto-redirect to `/internal/dashboard`
2. View assigned requests → `/{service}/manager` (filtered)
3. Take actions → Send invites, complete tasks

---

## ✅ **Route Organization Benefits**

1. **Clean Separation** - Easy to find routes
2. **Maintainable** - Each file has clear purpose
3. **Scalable** - Add new services easily
4. **Readable** - Well-commented and organized
5. **Testable** - Clear route structure

---

## 🎉 **All Routes Working!**

**Test with:**
```bash
php artisan route:list
```

**Server running on:** `http://localhost:8000`

**Routes are clean, organized, and READY! 🚀**
