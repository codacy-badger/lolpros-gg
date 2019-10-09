<?php

namespace Deployer;

require 'recipe/symfony4.php';

set('application', 'lolpros');
set('repository', 'git@github.com:SpireGG/lolpros-gg.git');
set('git_tty', false);
set('allow_anonymous_stats', false);
set('default_timeout', 600);
set('writable_dirs', ['var/cache', 'var/log', 'var/sessions']);

// Hosts
host('146.185.137.177')
    ->user('chypriote')
    ->multiplexing(false)
    ->forwardAgent(true)
    ->set('deploy_path', '~/{{application}}');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'database:migrate',
    'deploy:writable',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
