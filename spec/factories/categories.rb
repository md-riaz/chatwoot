FactoryBot.define do
  factory :category, class: 'Category' do
    portal
    sequence(:name) { |n| "Category #{n}" }
    description { Faker::Lorem.sentence }
    position { 1 }
    slug { name.parameterize }

    after(:build) do |category|
      category.account ||= category.portal.account
    end
  end
end
