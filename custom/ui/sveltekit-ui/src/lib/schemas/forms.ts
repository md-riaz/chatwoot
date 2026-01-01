import { z } from 'zod';

// Login form schema
export const loginSchema = z.object({
	email: z.string().email('Invalid email address').min(1, 'Email is required'),
	password: z.string().min(1, 'Password is required'),
	rememberMe: z.boolean().optional().default(false)
});

// Account form schema
export const accountSchema = z.object({
	name: z.string().min(1, 'Account name is required').max(255),
	status: z.enum(['active', 'suspended']).default('active'),
	locale: z.string().min(1, 'Locale is required'),
	domain: z.string().optional(),
	auto_resolve_duration: z.number().int().positive().optional()
});

// User form schema
export const userSchema = z.object({
	name: z.string().min(1, 'Name is required').max(255),
	display_name: z.string().max(255).optional(),
	email: z.string().email('Invalid email address'),
	role: z.enum(['administrator', 'agent']),
	password: z.string().min(6, 'Password must be at least 6 characters').optional(),
	account_ids: z.array(z.number()).optional()
});

// Agent Bot form schema
export const agentBotSchema = z.object({
	name: z.string().min(1, 'Bot name is required').max(255),
	description: z.string().max(500).optional(),
	outgoing_url: z.string().url('Invalid URL').optional()
});

// Platform App form schema
export const platformAppSchema = z.object({
	name: z.string().min(1, 'App name is required').max(255),
	webhook_url: z.string().url('Invalid webhook URL')
});

// Access Token form schema
export const accessTokenSchema = z.object({
	name: z.string().min(1, 'Token name is required').max(255)
});

// Onboarding form schema
export const onboardingSchema = z.object({
	name: z.string().min(1, 'Name is required').max(255),
	company: z.string().min(1, 'Company name is required').max(255),
	email: z.string().email('Invalid email address'),
	password: z.string().min(6, 'Password must be at least 6 characters')
});

export type LoginFormData = z.infer<typeof loginSchema>;
export type AccountFormData = z.infer<typeof accountSchema>;
export type UserFormData = z.infer<typeof userSchema>;
export type AgentBotFormData = z.infer<typeof agentBotSchema>;
export type PlatformAppFormData = z.infer<typeof platformAppSchema>;
export type AccessTokenFormData = z.infer<typeof accessTokenSchema>;
export type OnboardingFormData = z.infer<typeof onboardingSchema>;
