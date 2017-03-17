set :application, ''
set :branch, 'master'

role :web, ""
server "", user: "", roles: %w{web}

namespace :deploy do
  before 'deploy:updated', 'gulp'
end
