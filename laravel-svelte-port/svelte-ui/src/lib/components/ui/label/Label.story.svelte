<script lang="ts">
  import { Label } from './index.js';
  import { Input } from '../input/index.js';
  import { Checkbox } from '../checkbox/index.js';
  import { RadioGroup } from '../radio-group/index.js';
  import { Textarea } from '../textarea/index.js';
  
  export let Hst: any;
</script>

<Hst.Story title="Primitives/Label" icon="lucide:tag">
  <Hst.Variant title="Default">
    <div class="flex flex-col gap-2 p-4">
      <Label>Default Label</Label>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Input">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="grid gap-2">
        <Label for="email">Email</Label>
        <Input type="email" id="email" placeholder="Enter your email" />
      </div>
      
      <div class="grid gap-2">
        <Label for="password">Password</Label>
        <Input type="password" id="password" placeholder="Enter your password" />
      </div>
      
      <div class="grid gap-2">
        <Label for="username">Username</Label>
        <Input type="text" id="username" placeholder="Choose a username" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Checkbox">
    <div class="flex flex-col gap-4 p-4">
      <div class="flex items-center gap-2">
        <Checkbox id="terms1" />
        <Label for="terms1" class="cursor-pointer">Accept terms and conditions</Label>
      </div>
      
      <div class="flex items-center gap-2">
        <Checkbox id="marketing" />
        <Label for="marketing" class="cursor-pointer">Send me marketing emails</Label>
      </div>
      
      <div class="flex items-center gap-2">
        <Checkbox id="newsletter" />
        <Label for="newsletter" class="cursor-pointer">Subscribe to newsletter</Label>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Textarea">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="grid gap-2">
        <Label for="message">Message</Label>
        <Textarea id="message" placeholder="Type your message here..." rows={4} />
      </div>
      
      <div class="grid gap-2">
        <Label for="bio">Bio</Label>
        <Textarea id="bio" placeholder="Tell us about yourself..." rows={3} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Description">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="grid gap-2">
        <Label for="name">Display Name</Label>
        <Input type="text" id="name" placeholder="John Doe" />
        <p class="text-sm text-muted-foreground">
          This is how your name will appear to others.
        </p>
      </div>
      
      <div class="grid gap-2">
        <Label for="url">Website URL</Label>
        <Input type="url" id="url" placeholder="https://example.com" />
        <p class="text-sm text-muted-foreground">
          Your personal or company website.
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Required Fields">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="grid gap-2">
        <Label for="required-name">
          Full Name <span class="text-destructive">*</span>
        </Label>
        <Input type="text" id="required-name" placeholder="Enter your full name" required />
      </div>
      
      <div class="grid gap-2">
        <Label for="required-email">
          Email Address <span class="text-destructive">*</span>
        </Label>
        <Input type="email" id="required-email" placeholder="you@example.com" required />
      </div>
      
      <div class="grid gap-2">
        <Label for="phone">
          Phone Number <span class="text-muted-foreground">(optional)</span>
        </Label>
        <Input type="tel" id="phone" placeholder="+1 (555) 123-4567" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled State">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="grid gap-2">
        <Label for="disabled-input" class="opacity-70">Disabled Input</Label>
        <Input type="text" id="disabled-input" placeholder="This is disabled" disabled />
      </div>
      
      <div class="flex items-center gap-2">
        <Checkbox id="disabled-check" disabled />
        <Label for="disabled-check" class="cursor-not-allowed opacity-70">
          This option is disabled
        </Label>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Form Example">
    <div class="p-4 max-w-md">
      <form class="space-y-4">
        <h3 class="text-lg font-semibold">Create Account</h3>
        
        <div class="grid gap-2">
          <Label for="form-name">Name <span class="text-destructive">*</span></Label>
          <Input type="text" id="form-name" required />
        </div>
        
        <div class="grid gap-2">
          <Label for="form-email">Email <span class="text-destructive">*</span></Label>
          <Input type="email" id="form-email" required />
        </div>
        
        <div class="grid gap-2">
          <Label for="form-password">Password <span class="text-destructive">*</span></Label>
          <Input type="password" id="form-password" required />
          <p class="text-sm text-muted-foreground">
            Must be at least 8 characters long
          </p>
        </div>
        
        <div class="flex items-center gap-2">
          <Checkbox id="form-terms" required />
          <Label for="form-terms" class="cursor-pointer">
            I agree to the <a href="#" class="text-primary underline">terms and conditions</a>
          </Label>
        </div>
      </form>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Label Sizes">
    <div class="flex flex-col gap-4 p-4">
      <div class="grid gap-2">
        <Label class="text-xs">Extra Small Label</Label>
        <Input placeholder="Input with xs label" />
      </div>
      
      <div class="grid gap-2">
        <Label class="text-sm">Small Label (default)</Label>
        <Input placeholder="Input with sm label" />
      </div>
      
      <div class="grid gap-2">
        <Label class="text-base">Base Label</Label>
        <Input placeholder="Input with base label" />
      </div>
      
      <div class="grid gap-2">
        <Label class="text-lg">Large Label</Label>
        <Input placeholder="Input with lg label" />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
