// Domain-level TypeScript interfaces for type safety

export interface User {
	id: number;
	name: string;
	display_name?: string;
	email: string;
	role: 'administrator' | 'agent';
	avatar_url?: string;
	locked?: boolean;
	email_confirmed?: boolean;
	created_at?: string;
	updated_at?: string;
}

export interface Account {
	id: number;
	name: string;
	status: 'active' | 'suspended';
	locale: string;
	domain?: string;
	auto_resolve_duration?: number;
	created_at?: string;
	updated_at?: string;
}

export interface DashboardData {
	accountsCount: number;
	usersCount: number;
	inboxesCount: number;
	conversationsCount: number;
	chartData?: Array<[string, number]>;
}

export interface AgentBot {
	id: number;
	name: string;
	description?: string;
	outgoing_url?: string;
	avatar_url?: string;
	created_at?: string;
	updated_at?: string;
}

export interface PlatformApp {
	id: number;
	name: string;
	webhook_url: string;
	token?: string;
	created_at?: string;
	updated_at?: string;
}

export interface AccessToken {
	id: number;
	name: string;
	token: string;
	created_at?: string;
}

export interface InstallationConfig {
	id: number;
	name: string;
	key: string;
	value: string;
	type: string;
	created_at?: string;
	updated_at?: string;
}

export interface AccountUser {
	id: number;
	user_id: number;
	account_id: number;
	user_name: string;
	user_email: string;
	account_name: string;
	role: string;
	created_at?: string;
}

export interface AuditLog {
	id: number;
	event: string;
	user: string;
	ip_address?: string;
	details?: string;
	timestamp: string;
}

export interface Setting {
	name: string;
	value: string | number | boolean;
	category?: string;
	description?: string;
}

export interface PaginationParams {
	page?: number;
	per_page?: number;
	query?: string;
	sort_by?: string;
	sort_order?: 'asc' | 'desc';
}

export interface PaginatedResponse<T> {
	data: T[];
	total: number;
	page: number;
	per_page: number;
	total_pages: number;
}

export interface ApiError {
	message: string;
	errors?: Record<string, string[]>;
	status?: number;
}

export interface AuthResponse {
	token: string;
	user: User;
}

export interface OnboardingData {
	needs_onboarding: boolean;
	admin_exists: boolean;
}

// Column render function type that returns safe content
export type SafeRenderFunction<T = unknown> = (value: unknown, row: T) => string | number | null | undefined;

export interface DataTableColumn<T = unknown> {
	key: string;
	label: string;
	sortable?: boolean;
	render?: SafeRenderFunction<T>;
}
