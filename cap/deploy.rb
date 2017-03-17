# config valid only for current version of Capistrano
lock '>=3.4.0'

# set app name and repo URL
#set :application, ''
set :repo_url, ''

set :deploy_to, -> { "/var/www/vhosts/#{fetch(:application)}/web" }
set :deploy_via, :copy
set :copy_exclude, [".git", ".DS_Store", ".gitignore", ".gitmodules"]

set :composer_install_flags, '--no-dev --no-interaction --optimize-autoloader'
set :npm_target_path, -> { "#{fetch(:release_path)}/web/app/themes/yetipress" }
set :npm_flags, '--silent --no-progress'

set :bower_target_path, -> { "#{fetch(:release_path)}/web/app/themes/yetipress" }

set :gulp_file, -> { release_path.join('web/app/themes/yetipress/gulpfile.js') }
set :gulp_tasks, 'build'

set :linked_files, %w{.env web/.htaccess}
set :linked_dirs, ["web/app/uploads"]

namespace :deploy do

  desc "Setting file permissions"
  task :set_file_permissions do
    on roles(:web) do
        execute "chmod", "775", release_path.join('web/app/plugins'), release_path.join('web/app/themes')
    end
  end

  desc "Cleaning up composer cache"
  task :clear_composer_cache do
    on roles(:web) do
        execute "composer", "clear-cache"
    end
  end

  before 'composer:install', 'deploy:clear_composer_cache'
  after 'deploy', 'deploy:set_file_permissions'
end
