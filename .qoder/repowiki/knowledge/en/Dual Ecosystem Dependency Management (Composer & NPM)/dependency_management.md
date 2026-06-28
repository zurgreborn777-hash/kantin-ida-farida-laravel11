This repository employs a standard dual-ecosystem dependency management strategy typical of modern Laravel applications, utilizing **Composer** for PHP backend dependencies and **NPM** for frontend JavaScript tooling.

### 1. PHP Dependency Management (Composer)
- **Manifest**: `composer.json` defines the project as `laravel/laravel` requiring PHP `^8.2` and Laravel Framework `^11.0`.
- **Locking**: `composer.lock` is present and committed, ensuring deterministic builds by locking transitive dependencies to specific versions (e.g., `brick/math`, `carbonphp/carbon-doctrine-types`).
- **Autoloading**: PSR-4 autoloading is configured for the `App\`, `Database\Factories\`, and `Database\Seeders\` namespaces.
- **Configuration**: 
  - `optimize-autoloader` is enabled for performance.
  - `preferred-install` is set to `dist` (zip archives) for faster installs.
  - `platform-check` is disabled, but `platform.php` is pinned to `8.2.0` to ensure compatibility with the target runtime environment.
  - `allow-plugins` explicitly permits `pestphp/pest-plugin` and `php-http/discovery`.

### 2. Frontend Dependency Management (NPM)
- **Manifest**: `package.json` is marked as `private: true` to prevent accidental publication. It uses ES modules (`type: "module"`).
- **Dependencies**: Minimal frontend stack focused on build tooling:
  - `vite` (^5.0) as the bundler.
  - `laravel-vite-plugin` (^1.0) for integration with Laravel.
  - `axios` (^1.6.4) for HTTP requests.
- **Locking**: `package-lock.json` (lockfileVersion 3) is committed to ensure consistent node_modules installation across environments.

### 3. Deployment & Environment Provisioning (Nixpacks)
- **Configuration**: `nixpacks.toml` orchestrates the build and runtime environment, likely for platforms like Railway or Render.
- **Phase Setup**: Explicitly installs PHP 8.2 with required extensions (pdo, mysql, mbstring, xml, curl, zip, gd, tokenizer, bcmath, fileinfo, openssl, intl) along with Composer and Node.js 20.
- **Installation Phase**: 
  - Runs `composer install --no-dev --optimize-autoloader --no-interaction` to install production PHP dependencies.
  - Runs `npm ci` to install frontend dependencies using the lockfile for strict reproducibility.
- **Build Phase**: Executes `npm run build` to compile assets via Vite.

### 4. Developer Conventions
- **Reproducibility**: Both `composer.lock` and `package-lock.json` are version-controlled, mandating that developers use `composer install` and `npm ci` (or `npm install` if modifying deps) to maintain environment parity.
- **Script Hooks**: Composer scripts automate post-install tasks such as package discovery, asset publishing, and environment file creation (`.env`).
- **No Vendoring**: Dependencies are not vendored in the repository; they are fetched from Packagist and NPM registries during the build/install phase.