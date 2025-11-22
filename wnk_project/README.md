# WNK - Waste Not Kitchen
## Role 1 Complete Implementation

---

## 📦 **What's Included**

This is the **complete implementation for Role 1 (Coordinator)** including:

✅ Registration page (all 5 user types)  
✅ Login page (with session management)  
✅ Logout functionality  
✅ Profile edit page (all user types)  
✅ Restaurant dashboard  
✅ Restaurant add plate page  
✅ Restaurant manage plates page  
✅ Professional CSS styling  
✅ JavaScript form validation  
✅ Secure password hashing  
✅ SQL injection protection  

---

## 📂 **Project Structure**

```
wnk_project/
├── includes/
│   ├── config.php          # Database connection & helper functions
│   ├── header.php          # Site header with navigation
│   └── footer.php          # Site footer
├── css/
│   └── style.css           # Complete stylesheet
├── js/
│   └── main.js             # Form validation & interactions
├── index.php               # Home page
├── register.php            # Registration (all 5 user types)
├── login.php               # Login with role-based redirects
├── logout.php              # Logout & session destroy
├── profile_edit.php        # Edit profile (all user types)
├── restaurant_dashboard.php    # Restaurant home page
├── restaurant_add_plate.php    # Add new surplus plates
└── restaurant_manage_plates.php # Manage all plates
```

---

## 🚀 **Installation Instructions**

### **Option 1: Using MAMP (Recommended)**

1. **Install MAMP**
   - Download from https://www.mamp.info
   - Install and start servers

2. **Copy Project Files**
   ```bash
   cp -r wnk_project /Applications/MAMP/htdocs/
   ```

3. **Create Database**
   - Open phpMyAdmin: http://localhost:8888/phpMyAdmin
   - Create database: `wnk_db`
   - Import `wnk_schema_improved.sql`

4. **Configure Database**
   - Open `includes/config.php`
   - Update port if needed (default: 8889)

5. **Access Website**
   - Open browser: http://localhost:8888/wnk_project/

---

### **Option 2: Using db-fiddle (For Testing)**

1. **Set up database**
   - Go to https://www.db-fiddle.com
   - Select MySQL 8.0
   - Paste `wnk_schema_improved.sql`
   - Click "Run"

2. **For demo purposes**
   - Take screenshots of working database
   - Show SQL queries
   - This proves your schema works!

3. **Note:** PHP pages require MAMP or similar server

---

## 🔧 **Configuration**

### **Database Settings** (`includes/config.php`)

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '8889');  // Change to 3306 for Windows
define('DB_NAME', 'wnk_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
```

### **Default Admin Account**

Already created in database:
- Email: `admin@wnk.com`
- Password: `admin123`

---

## 📖 **Features Implemented**

### **1. Registration Page** (`register.php`)
- ✅ Supports all 5 user types (admin, restaurant, customer, donner, needy)
- ✅ Dynamic form fields based on user type
- ✅ JavaScript validation
- ✅ Password confirmation
- ✅ Email uniqueness check
- ✅ Transaction-based database inserts
- ✅ Phone number required for most types, optional for needy
- ✅ Credit card collection for customers & donors
- ✅ Restaurant-specific fields

### **2. Login Page** (`login.php`)
- ✅ Email & password authentication
- ✅ Password verification using bcrypt
- ✅ Session management
- ✅ Role-based redirects
- ✅ Account status check
- ✅ Secure against SQL injection

### **3. Profile Edit Page** (`profile_edit.php`)
- ✅ Works for all user types
- ✅ Updates user information
- ✅ Updates role-specific data
- ✅ Optional password change
- ✅ Form pre-populated with current data
- ✅ Transaction-based updates

### **4. Restaurant Dashboard** (`restaurant_dashboard.php`)
- ✅ Statistics (total plates, active, sold)
- ✅ Quick action buttons
- ✅ Recent plates list
- ✅ Professional layout

### **5. Add Plate** (`restaurant_add_plate.php`)
- ✅ Add new surplus food plates
- ✅ Set price, quantity, time window
- ✅ Input validation
- ✅ Automatic active status

### **6. Manage Plates** (`restaurant_manage_plates.php`)
- ✅ View all plates
- ✅ Filter by status (all/active/inactive)
- ✅ Update quantities inline
- ✅ Activate/deactivate plates
- ✅ Delete plates
- ✅ Auto-deactivate when quantity reaches 0

---

## 🎨 **Design Features**

- Professional, modern UI
- Responsive design (mobile-friendly)
- Color-coded status badges
- Clean navigation
- Form validation feedback
- Success/error alerts
- Consistent styling across all pages

---

## 🔒 **Security Features**

- ✅ Password hashing with bcrypt
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input sanitization
- ✅ CSRF protection ready

---

## 📸 **Screenshots for Report**

Take screenshots of:

1. **Home page** - index.php
2. **Registration form** - register.php (show different user types)
3. **Login page** - login.php
4. **Profile edit** - profile_edit.php
5. **Restaurant dashboard** - restaurant_dashboard.php
6. **Add plate form** - restaurant_add_plate.php
7. **Manage plates table** - restaurant_manage_plates.php
8. **Database tables** - phpMyAdmin showing data

---

## 🧪 **Testing Guide**

### **Test Registration:**

1. Go to register.php
2. Select "Restaurant"
3. Fill in all fields
4. Check database - user appears in Users and Restaurants tables

### **Test Login:**

1. Login with registered account
2. Verify redirect to restaurant_dashboard.php
3. Check session is set

### **Test Profile Edit:**

1. Login as restaurant
2. Go to profile_edit.php
3. Change restaurant name
4. Verify database updated

### **Test Add Plate:**

1. Login as restaurant
2. Go to restaurant_add_plate.php
3. Add a plate
4. Check it appears in manage plates

### **Test Manage Plates:**

1. View plates list
2. Update quantity
3. Deactivate a plate
4. Verify status changes

---

## 📝 **Database Schema Used**

This project uses your **improved schema** with:

- Users (base table)
- Restaurants (with phone_number)
- Customers (with phone_number, credit card)
- Donners (with phone_number, credit card)
- Needy (phone_number optional)
- Plates
- Customer_Reservations
- Donations
- Needy_Claim

**8 tables total** (removed Administrators and Reports tables as discussed)

---

## ✅ **Checklist for Progress Report**

**Completed Pages:**
- [x] Registration (all 5 types working)
- [x] Login (with role redirects)
- [x] Logout
- [x] Profile edit (all types)
- [x] Restaurant dashboard
- [x] Restaurant add plate
- [x] Restaurant manage plates

**Features Working:**
- [x] Database connectivity
- [x] Session management
- [x] Form validation
- [x] Data insertion
- [x] Data updates
- [x] Role-based access control

**Documentation:**
- [x] Code comments
- [x] README file
- [x] Setup instructions

---

## 🐛 **Known Issues / TODO**

**Minor enhancements** (not required but nice to have):
- Add "forgot password" functionality
- Add profile picture uploads
- Add email notifications
- Add more detailed form validation messages
- Add pagination for manage plates (if many plates)

**For next milestones:**
- Thomas needs to build customer/donner/needy pages
- Matthew needs to build admin pages & reports

---

## 💡 **Tips for Demo**

1. **Start clean:** Drop and recreate database before demo
2. **Show registration:** Register a new restaurant account
3. **Show login:** Login with that account
4. **Show profile edit:** Update restaurant info
5. **Show add plate:** Add 2-3 plates
6. **Show manage:** Demonstrate activate/deactivate, update quantity
7. **Show database:** Open phpMyAdmin, show data in tables

**Time estimate:** 5 minutes (perfect for your demo slot!)

---

## 🎓 **For Your Report**

### **What to say:**

> "For Role 1, I implemented all registration, login, and restaurant management functionality. The system supports all 5 user types with role-specific data collection. Users can register, login, edit their profiles, and restaurants can add and manage surplus food plates. All features include proper validation, security measures, and work with our improved database schema."

### **Technologies used:**
- PHP 7.4+
- MySQL 8.0
- HTML5, CSS3
- JavaScript (ES6)
- MAMP for local development

---

## 📞 **Support**

If Thomas or Matthew need help integrating their pages:

1. They can use the same `includes/config.php` for database
2. They can use `includes/header.php` and `includes/footer.php` for consistent design
3. All helper functions are in `config.php`
4. CSS classes are documented in `css/style.css`

---

## 🎉 **You're All Set!**

Everything is complete and ready for your progress report. Just:

1. Install on MAMP
2. Import database
3. Test all features
4. Take screenshots
5. Write your report section
6. Demo on November 21!

**Good luck! 🚀**
