# Contributing to WordPress Theme Framework

Thank you for considering contributing to the WordPress Theme Framework!

## Code of Conduct

Please be respectful and considerate of others when submitting issues or pull requests.

## How to Contribute

1. **Fork the Repository** on GitHub: [https://github.com/kulangaraalwinjoy/ThemeFramework](https://github.com/kulangaraalwinjoy/ThemeFramework)
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/<your-username>/ThemeFramework.git
   cd ThemeFramework
   ```
3. **Install Dependencies**:
   ```bash
   composer install
   ```
4. **Create a Feature Branch**:
   ```bash
   git checkout -b feature/my-new-feature
   ```
5. **Run the Quality Gates**:
   Before submitting your PR, ensure all checks pass:
   ```bash
   composer test     # Run PHPUnit test suite
   composer lint     # Run PHP_CodeSniffer with WordPress Coding Standards
   composer analyse  # Run PHPStan static analysis (Level 7)
   ```
6. **Commit & Push**:
   ```bash
   git commit -m "feat: add descriptive feature"
   git push origin feature/my-new-feature
   ```
7. **Submit a Pull Request** to the `main` branch.

## Architecture Principle

Remember the core rule: **ONE FIELD ENGINE**. Do not create separate field engines for posts, menus, or options. New field types must implement `FieldTypeInterface` and be agnostic of storage contexts.

⚡ *Proudly Vibe Coded by Alwin Joy.*
