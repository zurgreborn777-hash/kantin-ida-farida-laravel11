## Build System Overview

This project is a **Laravel 11** application utilizing standard PHP and Node.js tooling for dependency management, asset compilation, and deployment. The build system relies on **Composer** for backend dependencies, **Vite** for frontend asset bundling, and **Nixpacks** for containerized deployment configuration.

### Core Tools & Dependencies
- **Backend**: PHP 8.2+ managed via `composer.json`. Key dependencies include `laravel/framework` (^11.0) and `laravel/tinker`.
- **Frontend**: Node.js 20+ managed via `package.json`. Assets (CSS/JS) are bundled using **Vite** (^5.0) with the `laravel-vite-plugin`.
- **Testing**: PHPUnit (^10.5) configured via `phpunit.xml` with separate Unit and Feature test suites.
- **Deployment**: Configured for **Nixpacks** (likely for platforms like Railway or Render) via `nixpacks.toml`, which defines the build environment, installation steps, and start command.

### Build & Asset Compilation
- **Development**: Run `npm run dev` to start the Vite development server with hot-module replacement.
- **Production Build**: Run `npm run build` to compile and minify assets into the `public/build` directory.
- **Dependency Installation**: 
  - Backend: `composer install --no-dev --optimize-autoloader` (production).
  - Frontend: `npm ci` (production) or `npm install` (development).

### Deployment Configuration (Nixpacks)
The `nixpacks.toml` file orchestrates the deployment process:
1. **Setup**: Installs PHP 8.2 with required extensions (pdo, mysql, mbstring, etc.), Composer, Node.js 20, and npm.
2. **Install**: Runs `composer install` (without dev dependencies) and `npm ci`.
3. **Build**: Executes `npm run build` to compile frontend assets.
4. **Start**: Runs a chained command to:
   - Link storage (`php artisan storage:link`).
   - Run database migrations (`php artisan migrate --force`).
   - Seed the database (`php artisan db:seed --force`).
   - Clear optimizations (`php artisan optimize:clear`).
   - Start the Laravel server (`php artisan serve --host=0.0.0.0 --port=$PORT`).

### Testing Strategy
- **Framework**: PHPUnit.
- **Configuration**: `phpunit.xml` sets up an isolated testing environment using array-based cache/session drivers and a low bcrypt round count for speed.
- **Execution**: Tests are divided into `tests/Unit` and `tests/Feature` directories. Run `vendor/bin/phpunit` or `php artisan test` to execute the suite.

### Developer Conventions
- **Environment Setup**: Copy `.env.example` to `.env` and generate an application key using `php artisan key:generate`.
- **Database**: Uses SQLite by default (`database/kantin.db`) for local development, as indicated by the presence of the file and migration scripts.
- **Code Style**: Laravel Pint is included as a dev dependency for PHP code formatting.
- **Autoloading**: PSR-4 autoloading is configured for `App\` (app/), `Database\Factories\` (database/factories/), and `Database\Seeders\` (database/seeders/).
