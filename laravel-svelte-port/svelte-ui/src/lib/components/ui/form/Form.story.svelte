<script lang="ts">
  import * as Form from './index.js';
  import { Input } from '../input/index.js';
  import { Textarea } from '../textarea/index.js';
  import { Button } from '../button/index.js';
  import { Checkbox } from '../checkbox/index.js';
  import { Select } from '../select/index.js';
  
  export let Hst: any;
  
  let formData = $state({
    username: '',
    email: '',
    password: '',
    bio: '',
    terms: false,
    country: ''
  });
  
  let errors = $state<Record<string, string>>({});
  
  function validateForm() {
    errors = {};
    
    if (!formData.username) {
      errors.username = 'Username is required';
    } else if (formData.username.length < 3) {
      errors.username = 'Username must be at least 3 characters';
    }
    
    if (!formData.email) {
      errors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      errors.email = 'Please enter a valid email';
    }
    
    if (!formData.password) {
      errors.password = 'Password is required';
    } else if (formData.password.length < 8) {
      errors.password = 'Password must be at least 8 characters';
    }
    
    if (!formData.terms) {
      errors.terms = 'You must accept the terms and conditions';
    }
    
    return Object.keys(errors).length === 0;
  }
  
  function handleSubmit(e: Event) {
    e.preventDefault();
    if (validateForm()) {
      alert('Form submitted successfully!');
    }
  }
</script>

<Hst.Story title="Primitives/Form" icon="lucide:file-text">
  <Hst.Variant title="Basic Form">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md">
        <Form.Root>
          <Form.Field name="username">
            <Form.Label for="username">Username</Form.Label>
            <Input id="username" placeholder="Enter your username" />
            <Form.Description>
              This is your public display name.
            </Form.Description>
          </Form.Field>
          
          <Form.Field name="email">
            <Form.Label for="email">Email</Form.Label>
            <Input id="email" type="email" placeholder="you@example.com" />
          </Form.Field>
          
          <Button type="submit">Submit</Button>
        </Form.Root>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Validation">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md">
        <Form.Root onsubmit={handleSubmit}>
          <Form.Field name="username">
            <Form.Label for="username-v" required>Username</Form.Label>
            <Input
              id="username-v"
              bind:value={formData.username}
              placeholder="Enter your username"
              class={errors.username ? 'border-destructive' : ''}
            />
            <Form.Message error={errors.username} />
          </Form.Field>
          
          <Form.Field name="email">
            <Form.Label for="email-v" required>Email</Form.Label>
            <Input
              id="email-v"
              type="email"
              bind:value={formData.email}
              placeholder="you@example.com"
              class={errors.email ? 'border-destructive' : ''}
            />
            <Form.Message error={errors.email} />
          </Form.Field>
          
          <Form.Field name="password">
            <Form.Label for="password-v" required>Password</Form.Label>
            <Input
              id="password-v"
              type="password"
              bind:value={formData.password}
              placeholder="Enter your password"
              class={errors.password ? 'border-destructive' : ''}
            />
            <Form.Message error={errors.password} />
            <Form.Description>
              Must be at least 8 characters long.
            </Form.Description>
          </Form.Field>
          
          <div class="flex items-center gap-2">
            <Checkbox id="terms-v" bind:checked={formData.terms} />
            <Form.Label for="terms-v" class="text-sm font-normal cursor-pointer">
              I accept the terms and conditions
            </Form.Label>
          </div>
          <Form.Message error={errors.terms} />
          
          <Button type="submit" class="w-full">Create Account</Button>
        </Form.Root>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Complex Form">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <Form.Root>
          <div class="grid gap-6 md:grid-cols-2">
            <Form.Field name="firstName">
              <Form.Label for="firstName">First Name</Form.Label>
              <Input id="firstName" placeholder="John" />
            </Form.Field>
            
            <Form.Field name="lastName">
              <Form.Label for="lastName">Last Name</Form.Label>
              <Input id="lastName" placeholder="Doe" />
            </Form.Field>
          </div>
          
          <Form.Field name="email2">
            <Form.Label for="email2">Email Address</Form.Label>
            <Input id="email2" type="email" placeholder="john@example.com" />
            <Form.Description>
              We'll never share your email with anyone else.
            </Form.Description>
          </Form.Field>
          
          <Form.Field name="bio2">
            <Form.Label for="bio2">Bio</Form.Label>
            <Textarea
              id="bio2"
              placeholder="Tell us about yourself..."
              rows={4}
            />
            <Form.Description>
              Brief description for your profile. Max 500 characters.
            </Form.Description>
          </Form.Field>
          
          <div class="flex gap-2">
            <Button type="submit">Save Changes</Button>
            <Button type="button" variant="outline">Cancel</Button>
          </div>
        </Form.Root>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Form States">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md space-y-8">
        <div>
          <h3 class="text-sm font-medium mb-4">Success State</h3>
          <Form.Field name="success">
            <Form.Label for="success-input">Email</Form.Label>
            <Input
              id="success-input"
              value="user@example.com"
              class="border-success focus-visible:ring-success"
            />
            <p class="text-sm text-success mt-1">Email is valid!</p>
          </Form.Field>
        </div>
        
        <div>
          <h3 class="text-sm font-medium mb-4">Error State</h3>
          <Form.Field name="error">
            <Form.Label for="error-input">Username</Form.Label>
            <Input
              id="error-input"
              value="ab"
              class="border-destructive focus-visible:ring-destructive"
            />
            <Form.Message error="Username must be at least 3 characters" />
          </Form.Field>
        </div>
        
        <div>
          <h3 class="text-sm font-medium mb-4">Disabled State</h3>
          <Form.Field name="disabled">
            <Form.Label for="disabled-input">Read Only Field</Form.Label>
            <Input
              id="disabled-input"
              value="Cannot edit this"
              disabled
            />
          </Form.Field>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Inline Form">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <Form.Root class="flex gap-2 items-end">
          <Form.Field name="search" class="flex-1">
            <Form.Label for="search">Search</Form.Label>
            <Input id="search" placeholder="Search..." />
          </Form.Field>
          <Button type="submit">Search</Button>
        </Form.Root>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
