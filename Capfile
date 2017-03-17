set :deploy_config_path, 'cap/deploy.rb'
set :stage_config_path, 'cap/stages'

require 'capistrano/setup'
require 'capistrano/deploy'
require 'capistrano/composer'
require 'capistrano/bower'
require 'capistrano/npm'
require 'capistrano/gulp'

# Load custom tasks from `lib/capistrano/tasks` if you have any defined
Dir.glob('cap/tasks/*.rb').each { |r| import r }
