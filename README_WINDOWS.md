# Windows Guide

## Laravel `rename()` File Lock Workaround

### Background

On some Windows environments (especially managed corporate machines with endpoint monitoring, DLP, or antivirus software), newly created files may be briefly locked immediately after they are written.

Laravel performs atomic file replacement using:

```php
rename($tempPath, $path);
```

During this short period, Windows may throw:

```text
The process cannot access the file because it is being used by another process.
(code: 32)
```

Common examples include:

```text
storage/framework/views/*.php
bootstrap/cache/services.php
bootstrap/cache/packages.php
```

On our development environment, this was caused by the company monitoring service:

```text
Network Activity Manager (netactive32.dll)
```

which briefly scans newly created files before releasing the file handle.

---

## Solution

Modify Laravel's `replace()` method to retry the `rename()` operation for a short period.

File:

```text
vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php
```

Locate the following code:

```php
rename($tempPath, $path);
```

Replace it with:

```php
// Windows workaround:
// Some endpoint security / DLP software briefly locks newly-created files,
// causing rename() to fail with ERROR_SHARING_VIOLATION (code 32).
// Retry the rename for a short period before failing.

$renamed = false;
$lastError = null;

for ($attempt = 1; $attempt <= 30; $attempt++) {
    clearstatcache(true, $tempPath);
    clearstatcache(true, $path);

    if (@rename($tempPath, $path)) {
        $renamed = true;
        break;
    }

    $lastError = error_get_last();

    // Wait 100 ms before retrying.
    usleep(100000);
}

if (! $renamed) {
    throw new RuntimeException(
        $lastError['message']
            ?? "Unable to rename [{$tempPath}] to [{$path}]."
    );
}
```

---

## Why this works

The file is not permanently locked.

A monitoring service briefly opens every newly created file, preventing Windows from renaming it during that short interval.

Waiting a few hundred milliseconds before retrying allows the monitoring software to finish scanning and release the file handle.

The retry loop makes the operation resilient without changing Laravel's normal behavior.

---

## Notes

- This workaround is intended **only for Windows development environments**.
- Linux and macOS generally do not experience this issue because file renames are atomic even when another process has the file open.
- This change is made inside Laravel's `vendor` directory. It may be overwritten during future framework upgrades or Composer updates.
- After upgrading Laravel, verify that the retry logic is still present or reapply the patch if necessary.
- The preferred long-term solution is to exclude the following directories from endpoint monitoring (if permitted by your organization's IT policy):

```text
storage/framework/views
bootstrap/cache
```

However, on managed corporate machines this is often not possible.