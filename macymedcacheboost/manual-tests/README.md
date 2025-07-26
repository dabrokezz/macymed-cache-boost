# Manual Tests

This directory contains simple scripts that verify parts of the module without requiring a full PrestaShop installation.

## verify_configuration_service.php

Checks the behaviour of `ConfigurationService::get()` using a stub `Configuration` class. To run the script:

```bash
php manual-tests/verify_configuration_service.php
```

Expected output:

```
string(12) "stored value"
string(7) "default"
string(7) "default"
```
