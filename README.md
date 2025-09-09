# Absensi Kelas

A web-based class attendance management system designed for schools to efficiently track student attendance. Built with PHP, MySQL, and Tailwind CSS, this system provides an intuitive interface for both teachers and students.

## Features

- **User Management**: Separate registration and login for teachers (guru) and students (siswa)
- **Class Management**: Organize students by classes
- **Attendance Tracking**: Record attendance with statuses (Hadir, Izin, Sakit, Alpha)
- **Real-time Reports**: Monitor attendance data in real-time
- **Photo Uploads**: Profile photos and selfie verification for attendance
- **Secure Authentication**: Password hashing and session management
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **Cross-Platform Compatibility**: Works on Windows, Linux, and macOS

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Tailwind CSS
- **Build Tool**: Node.js & npm (for CSS compilation)

## Prerequisites

Before running this project, make sure you have the following installed:

- **PHP 7.4 or higher**
- **MySQL 5.7 or higher**
- **Node.js 14+ and npm** (for building CSS)
- **Web server** (Apache recommended) or use XAMPP/WAMP/MAMP

### Cross-Platform Setup

This application is designed to run on multiple platforms:

- **Windows**: Use XAMPP (https://www.apachefriends.org/)
- **Linux**: Install Apache, PHP, and MySQL via package manager (apt, yum, etc.)
- **macOS**: Use MAMP (https://www.mamp.info/) or Homebrew to install required components

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Arvin-Inc/project-web-absensi.git
   cd project-web-absensi
   ```

2. **Install Node.js dependencies** (for CSS compilation)
   ```bash
   npm install
   ```

3. **Build CSS**
   ```bash
   npm run build-css
   ```
   Or for development with watch mode:
   ```bash
   npm run build-css
   ```

4. **Set up the web server**
   - Copy the project to your web server's document root
   - For XAMPP: Copy to `htdocs/` folder
   - For Apache: Copy to `/var/www/html/` (Linux) or appropriate directory

## Database Setup

1. **Create Database**
   - Open phpMyAdmin or MySQL command line
   - Create a new database named `db_9009`

2. **Import Schema**
   - Import the `database.sql` file located in the project root
   - This will create all necessary tables and sample data

3. **Configure Database Connection**
   - Open `config/db.php`
   - Update the database credentials if necessary:
     ```php
     $host = 'localhost';
     $user = 'your_username';
     $pass = 'your_password';
     $db = 'db_9009';
     ```

## Running the Application

1. **Start your web server**
   - For XAMPP: Start Apache and MySQL modules
   - For other setups: Ensure Apache/PHP/MySQL services are running

2. **Access the application**
   - Open your browser and navigate to: `http://localhost/ppk-project/public/`
   - Or if using a different port: `http://localhost:8080/ppk-project/public/`

3. **Default Access**
   - Register new users or use sample data from `database.sql`
   - Sample teacher: `guru@example.com`
   - Sample students: `siswa1@example.com`, `siswa2@example.com`

## Usage

### For Teachers (Guru)
- Login with teacher credentials
- Generate attendance codes for specific dates
- View attendance reports for their classes
- Manage student information

### For Students (Siswa)
- Login with student credentials
- Mark attendance using generated codes
- Upload selfies for verification
- View personal attendance history

## Project Structure

```
ppk-project/
├── assets/                 # Static assets (images, uploads)
├── config/                 # Database and security configuration
├── includes/               # PHP includes (auth, functions)
├── logs/                   # Application logs
├── public/                 # Public web files
│   ├── css/               # Compiled CSS files
│   ├── *.php              # PHP pages
│   └── index.php          # Landing page
├── database.sql           # Database schema and sample data
├── package.json           # Node.js dependencies
├── tailwind.config.js     # Tailwind CSS configuration
└── README.md              # This file
```

## Security Features

- Password hashing using bcrypt
- Session management
- Input validation and sanitization
- SQL injection prevention with prepared statements
- File upload restrictions
- Security logging

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the ISC License - see the [LICENSE](LICENSE) file for details.

## Support

If you encounter any issues or have questions:

- Check the [Issues](https://github.com/Arvin-Inc/project-web-absensi/issues) page
- Create a new issue with detailed information
- Contact the maintainers

---

**Note**: This application is designed for educational purposes and should be deployed in a secure environment for production use.
