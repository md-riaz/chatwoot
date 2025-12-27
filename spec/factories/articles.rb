FactoryBot.define do
  factory :article, class: 'Article' do
    account
    category { nil }
    portal
    locale { 'en' }
    association :author, factory: :user
    title { "#{Faker::Movie.title} #{SecureRandom.hex}" }
    content { Faker::Lorem.paragraphs(number: 3).join("\n\n") }
    description { Faker::Lorem.sentence }
    status { :published }
    views { 0 }
  end
end
