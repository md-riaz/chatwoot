<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Search Performance Optimization Implementation...\n\n";

try {
    // Test 1: Check if classes can be loaded
    echo "1. Testing class loading...\n";
    
    if (class_exists('App\Services\SearchService')) {
        echo "   ✓ SearchService class exists\n";
    } else {
        echo "   ✗ SearchService class not found\n";
    }
    
    if (class_exists('App\Services\SearchPerformanceService')) {
        echo "   ✓ SearchPerformanceService class exists\n";
    } else {
        echo "   ✗ SearchPerformanceService class not found\n";
    }
    
    if (class_exists('App\Services\PermissionFilterService')) {
        echo "   ✓ PermissionFilterService class exists\n";
    } else {
        echo "   ✗ PermissionFilterService class not found\n";
    }
    
    if (class_exists('App\Http\Controllers\Api\V1\SearchController')) {
        echo "   ✓ SearchController class exists\n";
    } else {
        echo "   ✗ SearchController class not found\n";
    }
    
    if (class_exists('App\Console\Commands\SearchPerformanceCommand')) {
        echo "   ✓ SearchPerformanceCommand class exists\n";
    } else {
        echo "   ✗ SearchPerformanceCommand class not found\n";
    }

    // Test 2: Check if files exist
    echo "\n2. Testing file existence...\n";
    
    $files = [
        'app/Services/SearchService.php',
        'app/Services/SearchPerformanceService.php',
        'app/Services/PermissionFilterService.php',
        'app/Http/Controllers/Api/V1/SearchController.php',
        'app/Console/Commands/SearchPerformanceCommand.php',
        'config/search.php',
        'database/migrations/2025_01_02_140000_add_performance_optimization_indexes.php',
        'database/migrations/2025_01_02_150000_enhance_search_performance_indexes.php',
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "   ✓ {$file}\n";
        } else {
            echo "   ✗ {$file} not found\n";
        }
    }

    // Test 3: Check migration files for syntax
    echo "\n3. Testing migration syntax...\n";
    
    $migrationFiles = [
        'database/migrations/2025_01_02_140000_add_performance_optimization_indexes.php',
        'database/migrations/2025_01_02_150000_enhance_search_performance_indexes.php',
    ];
    
    foreach ($migrationFiles as $file) {
        if (file_exists($file)) {
            $output = shell_exec("php -l {$file} 2>&1");
            if (strpos($output, 'No syntax errors') !== false) {
                echo "   ✓ {$file} - syntax OK\n";
            } else {
                echo "   ✗ {$file} - syntax error: {$output}\n";
            }
        }
    }

    // Test 4: Check search configuration
    echo "\n4. Testing search configuration...\n";
    if (file_exists('config/search.php')) {
        $config = include 'config/search.php';
        if (is_array($config)) {
            echo "   ✓ Search configuration is valid array\n";
            echo "   - Keys: " . implode(', ', array_keys($config)) . "\n";
        } else {
            echo "   ✗ Search configuration is not an array\n";
        }
    }

    echo "\n✓ Basic file and class structure tests completed!\n";
    echo "\nImplemented Features:\n";
    echo "- Enhanced SearchService with GIN index support\n";
    echo "- SearchPerformanceService for monitoring and optimization\n";
    echo "- Updated SearchController with error handling and caching\n";
    echo "- Search configuration file with performance settings\n";
    echo "- Database migrations for search performance indexes\n";
    echo "- Console command for search performance management\n";
    echo "- Comprehensive test suite for search functionality\n";
    
    echo "\nNext Steps:\n";
    echo "1. Run migrations: php artisan migrate\n";
    echo "2. Check search indexes: php artisan search:performance check\n";
    echo "3. Run benchmark: php artisan search:performance benchmark\n";
    echo "4. Run tests: php artisan test tests/Unit/Services/SearchServiceTest.php\n";

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}