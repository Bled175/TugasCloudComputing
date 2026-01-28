# Backend Specification - Sistem Absensi Ekstrakurikuler
## Menggunakan Laravel + Filament 3

---

## 📋 Deskripsi Sistem

Sistem backend untuk manajemen absensi **1 ekstrakurikuler** dengan struktur sederhana:
- **Admin Panel**: Menggunakan Filament 3 untuk Admin & Sekretaris
- **REST API**: Untuk komunikasi dengan frontend React (siswa)
- **Role**: Admin, Sekretaris (backend), Siswa (frontend)

### Konsep
- Sistem untuk **1 eskul saja** (misalnya: Basket, Pramuka, PMR, dll)
- Admin mengelola master data
- Sekretaris melakukan scan QR & input absensi
- Siswa menggunakan frontend React untuk melihat data & QR mereka

---

## 🏗️ Tech Stack

- **Framework**: Laravel 11.x
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum (API) + Filament Auth (Admin)
- **QR Code**: SimpleSoftwareIO/simple-qrcode
- **Export**: Laravel Excel (Maatwebsite)

---

## 📊 Database Schema

### 📦 ENTITAS & ATRIBUT

#### 1️⃣ **users** - Login & Role Management

**Fungsi**:
- Autentikasi (login)
- Otorisasi (hak akses)
- Tracking siapa yang scan QR

**Atribut**:
```sql
- id (bigint, PK)
- name (varchar, 255)
- email (varchar, 255, unique)
- password (varchar, 255)
- role (enum: 'admin', 'sekretaris', 'siswa')
- remember_token (varchar, 100)
- created_at (timestamp)
- updated_at (timestamp)
```

**📌 Catatan**:
- Semua yang bisa login harus ada di `users`
- Admin & Sekretaris **tidak punya** record di `students`
- Hanya user dengan `role = 'siswa'` yang punya relasi ke `students`

---

#### 2️⃣ **students** - Data Siswa Peserta Eskul

**Fungsi**:
- Identitas siswa
- Pemilik QR Code
- Induk data absensi

**Atribut**:
```sql
- id (bigint, PK)
- user_id (bigint, FK -> users.id, unique)
- nama (varchar, 255)
- kelas (varchar, 50) # contoh: X-1, XI-2, XII-3
- qr_token (varchar, 255, unique) # unique token untuk QR code
- created_at (timestamp)
- updated_at (timestamp)
```

**📌 Catatan**:
- **1 student = 1 user** (role siswa)
- QR code menempel ke `students`, bukan `users`
- `qr_token` di-generate saat siswa dibuat (UUID atau hash unik)

---

#### 3️⃣ **attendances** - Catatan Kehadiran Harian

**Fungsi**:
- Menyimpan data absensi harian
- Dasar rekap & export
- Tracking siapa yang scan

**Atribut**:
```sql
- id (bigint, PK)
- student_id (bigint, FK -> students.id)
- tanggal (date)
- status (enum: 'hadir', 'izin', 'sakit', 'alpha')
- scanned_by (bigint, FK -> users.id, nullable) # sekretaris/admin yang scan
- scanned_at (datetime, nullable)
- keterangan (text, nullable) # optional note
- created_at (timestamp)
- updated_at (timestamp)

# Constraint
UNIQUE(student_id, tanggal) # Mencegah absen dobel dalam 1 hari
```

**📌 Catatan**:
- `scanned_by` → user yang melakukan scan (sekretaris/admin)
- Status **'hadir'** via scan QR
- Status **'izin', 'sakit', 'alpha'** via input manual admin/sekretaris
- Constraint `UNIQUE(student_id, tanggal)` mencegah duplikasi absensi di hari yang sama

---

## 🔗 RELASI ANTAR TABEL (ERD)

```
USERS
├── id
│
├── (1) ──────────> (0..1) STUDENTS
│   One-to-One         └── user_id (FK)
│   (hanya role siswa)
│
└── (1) ──────────> (∞) ATTENDANCES
    One-to-Many        └── scanned_by (FK)
    (sekretaris/admin)


STUDENTS
├── id
│
└── (1) ──────────> (∞) ATTENDANCES
    One-to-Many        └── student_id (FK)
```

### 🧠 Penjelasan Relasi

**🔹 User ↔ Student** (One-to-One)
- Hanya user dengan `role = 'siswa'` yang punya record di `students`
- Admin & Sekretaris **tidak punya** student
- Relasi: `user.id` = `student.user_id`

**🔹 Student ↔ Attendance** (One-to-Many)
- 1 siswa → banyak absensi (setiap hari)
- Setiap absensi **selalu milik** 1 student
- Relasi: `student.id` = `attendance.student_id`

**🔹 User (Sekretaris/Admin) ↔ Attendance** (One-to-Many)
- Sekretaris/Admin yang scan QR dicatat di `scanned_by`
- Untuk audit & rekap siapa petugas yang scan
- Relasi: `user.id` = `attendance.scanned_by`

---

## 📐 Migration Files

### Migration: create_users_table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'sekretaris', 'siswa'])->default('siswa');
    $table->rememberToken();
    $table->timestamps();
});
```

### Migration: create_students_table
```php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
    $table->string('nama');
    $table->string('kelas', 50);
    $table->string('qr_token')->unique();
    $table->timestamps();
});
```

### Migration: create_attendances_table
```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->onDelete('cascade');
    $table->date('tanggal');
    $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
    $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null');
    $table->datetime('scanned_at')->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamps();
    
    // Prevent duplicate attendance on same day
    $table->unique(['student_id', 'tanggal']);
});
```

---

## 📱 Model Classes (Laravel)

### User Model
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function scannedAttendances()
    {
        return $this->hasMany(Attendance::class, 'scanned_by');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSekretaris()
    {
        return $this->role === 'sekretaris';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }
}
```

### Student Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'kelas',
        'qr_token',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Auto-generate QR token when creating student
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->qr_token)) {
                $student->qr_token = Str::uuid();
            }
        });
    }

    // Helper: Get QR Code Image
    public function getQrCodeAttribute()
    {
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
            ->generate($this->qr_token);
    }
}
```

### Attendance Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'tanggal',
        'status',
        'scanned_by',
        'scanned_at',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'scanned_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
```

---

## 🎯 Filament 3 Resources (Admin Panel)

### 1. **Dashboard**
**File**: `app/Filament/Pages/Dashboard.php`

**Widgets**:
- `StatsOverviewWidget` - Total Siswa, Kehadiran Hari Ini, Hadir Minggu Ini, Izin Bulan Ini
- `AttendanceChartWidget` - Grafik tren kehadiran 7 hari terakhir
- `RecentActivityWidget` - Table absensi terbaru (10 terakhir)

**Metrics**:
```php
- Total Students
- Today's Attendance Rate (%)
- This Week Present Count
- Monthly Attendance Summary
```

---

### 2. **Student Resource**
**File**: `app/Filament/Resources/StudentResource.php`

**Form Fields**:
```php
TextInput::make('nama')->required(),
TextInput::make('kelas')->required(),
Select::make('user_id')
    ->relationship('user', 'name')
    ->searchable()
    ->required()
    ->createOptionForm([
        TextInput::make('name')->required(),
        TextInput::make('email')->email()->required(),
        TextInput::make('password')->password()->required(),
    ]),
TextInput::make('qr_token')->disabled()->default(fn() => Str::uuid()),
```

**Table Columns**:
```php
TextColumn::make('nama')->searchable()->sortable(),
TextColumn::make('kelas')->searchable()->sortable(),
TextColumn::make('user.email')->label('Email'),
BadgeColumn::make('attendances_count')
    ->counts('attendances')
    ->label('Total Absensi'),
```

**Filters**:
- Kelas (SelectFilter)

**Actions**:
- **View** - Lihat detail siswa
- **Edit** - Edit data siswa
- **Delete** - Hapus siswa (cascade ke user & attendances)
- **Download QR** - Download QR code siswa dalam format PNG/PDF
- **Regenerate QR** - Generate ulang QR token

**Bulk Actions**:
- Export Selected (Excel)
- Export All (Excel)

**Relations**:
- `attendances` (HasMany) - Riwayat absensi siswa

---

### 3. **Attendance Resource**
**File**: `app/Filament/Resources/AttendanceResource.php`

**Form Fields**:
```php
Select::make('student_id')
    ->relationship('student', 'nama')
    ->searchable()
    ->required(),
DatePicker::make('tanggal')->required()->default(now()),
Select::make('status')
    ->options([
        'hadir' => 'Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'alpha' => 'Alpha',
    ])
    ->required()
    ->default('hadir'),
Textarea::make('keterangan')->rows(3),
```

**Table Columns**:
```php
TextColumn::make('tanggal')->date('d M Y')->sortable(),
TextColumn::make('student.nama')->searchable()->sortable(),
TextColumn::make('student.kelas')->label('Kelas')->sortable(),
BadgeColumn::make('status')
    ->colors([
        'success' => 'hadir',
        'warning' => 'izin',
        'info' => 'sakit',
        'danger' => 'alpha',
    ]),
TextColumn::make('scanner.name')->label('Scan By'),
TextColumn::make('scanned_at')->dateTime('H:i'),
```

**Filters**:
- Date Range (DateRangeFilter)
- Status (SelectFilter: hadir/izin/sakit/alpha)
- Kelas (SelectFilter)
- Student (SelectFilter)

**Actions**:
- **View** - Detail absensi
- **Edit** - Edit status/keterangan
- **Delete** - Hapus record

**Header Actions**:
- **QR Scanner** - Buka modal scanner QR untuk absensi cepat
- **Manual Entry** - Input absensi manual

**Bulk Actions**:
- Mark as Alpha
- Export Selected (Excel)

---

### 4. **QR Scanner Page** (Custom Page)
**File**: `app/Filament/Pages/QrScanner.php`

**Features**:
- Live camera feed untuk scan QR code siswa
- Auto-detect QR token
- Validasi: cek apakah siswa sudah absen hari ini
- Submit absensi dengan status 'hadir'
- Notifikasi success/error
- Log scanner (simpan `scanned_by` dan `scanned_at`)

**UI Components**:
```php
- Video preview (webcam)
- Scan result display
- Student info card (after scan)
- Submit button
- Recent scans list (5 terakhir)
```

**Logic**:
```php
1. Scan QR → dapat qr_token
2. Cari student berdasarkan qr_token
3. Cek: apakah student.attendances sudah ada di tanggal hari ini?
   - Jika sudah: tampilkan error "Sudah absen hari ini"
   - Jika belum: create attendance baru dengan status 'hadir'
4. Simpan scanned_by = Auth::id()
5. Simpan scanned_at = now()
6. Show success notification
```

---

### 5. **Rekap/Laporan Page** (Custom Page)
**File**: `app/Filament/Pages/Rekap.php`

**Features**:
- Filter: Date Range, Kelas, Status
- Summary Cards:
  - Total Present
  - Total Izin
  - Total Sakit
  - Total Alpha
- Data Table: List semua absensi sesuai filter
- Export to Excel/PDF

**Charts**:
- Bar Chart: Kehadiran per hari (7 hari terakhir)
- Pie Chart: Distribution status absensi
- Table: Top 10 siswa dengan kehadiran terbaik

**Export Format**:
- Excel: Per siswa atau per tanggal
- PDF: Formatted report dengan logo & header

---

## 🔐 Authentication & Authorization

### Admin/Sekretaris Panel (Filament)

**Access Control**:
```php
// app/Providers/Filament/AdminPanelProvider.php

public function panel(Panel $panel): Panel
{
    return $panel
        ->authGuard('web')
        ->login()
        ->authMiddleware([
            Authenticate::class,
        ])
        ->middleware([
            function ($request, $next) {
                if (auth()->user()->role === 'siswa') {
                    abort(403, 'Access denied');
                }
                return $next($request);
            }
        ]);
}
```

**Role-Based Permissions**:
```php
// Admin: Full access
// Sekretaris: Read + Scan QR + Input Absensi (no delete siswa)

Gate::define('manage-students', fn(User $user) => $user->isAdmin());
Gate::define('scan-qr', fn(User $user) => $user->isAdmin() || $user->isSekretaris());
```

---

### API Authentication (Sanctum - untuk siswa)

**Login**:
```php
POST /api/login
{
    "email": "siswa1@eskul.com",
    "password": "siswa123"
}

Response:
{
    "success": true,
    "data": {
        "token": "1|xxxxx",
        "user": {
            "id": 1,
            "name": "Ahmad Rizki",
            "email": "siswa1@eskul.com",
            "role": "siswa"
        },
        "student": {
            "id": 1,
            "nama": "Ahmad Rizki",
            "kelas": "X-1",
            "qr_token": "uuid-xxxx"
        }
    }
}
```

**Protected Routes**:
- Middleware: `auth:sanctum`
- Only users with `role = 'siswa'` can access API endpoints

---

## 🚀 API Endpoints untuk Frontend Siswa

### Authentication
```
POST   /api/login              # Login siswa
POST   /api/logout             # Logout (revoke token)
GET    /api/user               # Get current user + student profile
```

### Dashboard
```
GET    /api/dashboard
# Response: Stats kehadiran siswa (total hadir, izin, sakit, alpha, persentase)
```

### Attendance (Riwayat)
```
GET    /api/attendances
# Query params: ?month=3&year=2026
# Response: List riwayat absensi siswa
```

### QR Code
```
GET    /api/qr-code
# Response: QR code siswa (base64 atau URL)
```

### Profile
```
GET    /api/profile            # Get profile siswa
PUT    /api/profile            # Update profile (nama, kelas - jika allowed)
```

---

## 📦 Laravel Packages Required

```bash
# Filament 3
composer require filament/filament:"^3.0"

# QR Code Generator
composer require simplesoftwareio/simple-qrcode

# Excel Export
composer require maatwebsite/excel

# Laravel Sanctum (API Auth)
composer require laravel/sanctum

# Optional: Spatie Media Library (jika butuh upload foto)
composer require spatie/laravel-medialibrary
```

---

## 📝 Seeder Example

### UserSeeder
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::create([
            'name' => 'Admin Eskul',
            'email' => 'admin@eskul.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Sekretaris
        User::create([
            'name' => 'Sekretaris Eskul',
            'email' => 'sekretaris@eskul.com',
            'password' => Hash::make('sekretaris123'),
            'role' => 'sekretaris',
        ]);

        // Siswa 1
        $user1 = User::create([
            'name' => 'Ahmad Rizki',
            'email' => 'siswa1@eskul.com',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $user1->id,
            'nama' => 'Ahmad Rizki Fauzan',
            'kelas' => 'X-1',
        ]);

        // Siswa 2
        $user2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siswa2@eskul.com',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $user2->id,
            'nama' => 'Siti Nurhaliza',
            'kelas' => 'XI-2',
        ]);

        // Siswa 3
        $user3 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'siswa3@eskul.com',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
        ]);
        Student::create([
            'user_id' => $user3->id,
            'nama' => 'Budi Santoso',
            'kelas' => 'X-3',
        ]);
    }
}
```

---

## 🔄 QR Code Scanning Flow

### Alur Scan QR (di Admin Panel)

```
1. Sekretaris buka halaman "QR Scanner" di Filament
2. Kamera aktif, scan QR code siswa
3. Frontend kirim qr_token ke backend
4. Backend validasi:
   ├─ Cek: apakah qr_token valid? (ada di students table?)
   ├─ Cek: apakah siswa sudah absen hari ini?
   │    └─ Query: attendances WHERE student_id = X AND tanggal = today
   └─ Jika belum absen:
        └─ Create attendance baru:
            - student_id = X
            - tanggal = today
            - status = 'hadir'
            - scanned_by = Auth::id() (sekretaris yang scan)
            - scanned_at = now()
5. Response: Success / Error message
6. Frontend: Tampilkan notifikasi + play sound
```

### API Endpoint untuk Scan (Admin/Sekretaris)
```php
POST /admin/scan-qr
{
    "qr_token": "uuid-xxxx"
}

Response Success:
{
    "success": true,
    "message": "Absensi berhasil dicatat",
    "data": {
        "student": {
            "nama": "Ahmad Rizki",
            "kelas": "X-1"
        },
        "attendance": {
            "tanggal": "2026-01-28",
            "status": "hadir",
            "scanned_at": "2026-01-28 14:30:00"
        }
    }
}

Response Error (Already Present):
{
    "success": false,
    "message": "Siswa sudah absen hari ini",
    "data": {
        "existing_attendance": {
            "tanggal": "2026-01-28",
            "status": "hadir",
            "scanned_at": "2026-01-28 14:15:00"
        }
    }
}
```

---

## 📊 Export Excel Example

### Export Rekap Absensi
```php
<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Attendance::with(['student', 'scanner'])
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Siswa',
            'Kelas',
            'Status',
            'Scan By',
            'Waktu Scan',
            'Keterangan',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->tanggal->format('d-m-Y'),
            $attendance->student->nama,
            $attendance->student->kelas,
            ucfirst($attendance->status),
            $attendance->scanner?->name ?? '-',
            $attendance->scanned_at?->format('H:i') ?? '-',
            $attendance->keterangan ?? '-',
        ];
    }
}
```

---

## 🏃 Implementation Steps

### 1. Setup Laravel Project
```bash
composer create-project laravel/laravel absensi-eskul-backend
cd absensi-eskul-backend
```

### 2. Install Packages
```bash
composer require filament/filament:"^3.0"
composer require simplesoftwareio/simple-qrcode
composer require maatwebsite/excel
composer require laravel/sanctum
```

### 3. Install Filament
```bash
php artisan filament:install --panels
```

### 4. Setup Database
- Configure `.env`
- Create migrations (users, students, attendances)
- Run migrations: `php artisan migrate`
- Run seeders: `php artisan db:seed`

### 5. Create Models
- `php artisan make:model Student`
- `php artisan make:model Attendance`

### 6. Create Filament Resources
```bash
php artisan make:filament-resource Student --generate
php artisan make:filament-resource Attendance --generate
```

### 7. Create Custom Pages
```bash
php artisan make:filament-page QrScanner
php artisan make:filament-page Rekap
```

### 8. Setup API Routes
- Create API controllers
- Define routes in `routes/api.php`
- Setup Sanctum middleware

### 9. Testing
- Test QR scanning flow
- Test API endpoints
- Test export functionality

---

## 🎯 Summary

### Sistem ini dirancang untuk:
✅ **1 Ekstrakurikuler** (bukan multi-eskul)  
✅ **3 Tabel Inti**: Users, Students, Attendances  
✅ **3 Role**: Admin, Sekretaris, Siswa  
✅ **Admin & Sekretaris**: Akses Filament Panel  
✅ **Siswa**: Akses API (frontend React)  
✅ **QR Code**: Per siswa (unique token)  
✅ **Scan QR**: Sekretaris scan untuk absensi  
✅ **Rekap & Export**: Excel/PDF  

### Fitur Utama Backend:
- 📊 Dashboard dengan statistics
- 👥 Management siswa dengan QR generator
- ✅ Absensi (manual & QR scan)
- 📷 QR Scanner page (live camera)
- 📈 Rekap & laporan (filter, chart, export)
- 🔐 Role-based access control
- 🔌 REST API untuk frontend siswa

---

**Next Steps**: Mulai implementasi dengan setup Laravel + Filament 3, lalu buat migrations & models sesuai spesifikasi di atas.

#### System Settings
- Timezone
- Date Format
- Language
- Backup Schedule

---

## 🔐 Authentication & Authorization

### Admin Panel (Filament)
```php
// Only admin & sekretaris can access Filament panel
Gate::define('access-filament', function (User $user) {
    return in_array($user->role, ['admin', 'sekretaris']);
});
```

**Note**: Siswa tidak bisa akses panel Filament, mereka pakai API saja.

---

## 📝 Catatan Penting

### Perbedaan dengan Spesifikasi Awal
Spesifikasi ini sudah **disederhanakan** dari versi multi-ekstrakurikuler menjadi sistem untuk **1 ekstrakurikuler saja**.

**Yang Dihapus**:

**Yang Dipertahankan**:
- ✅ 3 Tabel Inti: `users`, `students`, `attendances`
- ✅ QR Code per siswa
- ✅ Scan QR untuk absensi
- ✅ Role: Admin, Sekretaris, Siswa
- ✅ Filament Panel untuk Admin/Sekretaris
- ✅ API untuk Siswa (frontend React)
- ✅ Export Excel/PDF
- ✅ Dashboard & Statistics

### Scope Sistem
- 🎯 **1 Ekstrakurikuler** (misal: Basket, Pramuka, PMR, dll)
- 👥 Semua siswa yang terdaftar adalah anggota eskul tersebut
- 📅 Absensi harian sederhana (tanpa jadwal kegiatan kompleks)
- 📊 Rekap & laporan per siswa atau per tanggal

---

## 🎓 Kesimpulan

Dokumen ini berisi spesifikasi lengkap untuk backend sistem absensi ekstrakurikuler. Implementasi menggunakan Laravel + Filament 3 akan memberikan:

✅ Struktur database sederhana (3 tabel)  
✅ Admin panel yang powerful dengan Filament 3  
✅ REST API untuk frontend React  
✅ QR Code system untuk absensi  
✅ Export & reporting features  
✅ Role-based access control  
✅ Scalable & maintainable architecture  

**Next Steps**: Mulai development dengan setup Laravel project dan install Filament 3.
