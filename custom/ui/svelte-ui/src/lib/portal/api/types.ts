/**
 * Portal API Types
 * 
 * TypeScript interfaces for the portal (help center) API.
 */

export interface PortalConfig {
  portalSlug: string;
  name: string;
  description: string;
  logo?: string;
  primaryColor: string;
  locale: string;
  supportedLocales: string[];
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon?: string;
  position: number;
  locale: string;
  articleCount: number;
  parentCategoryId?: number;
}

export interface Article {
  id: number;
  title: string;
  content: string;
  description: string;
  slug: string;
  categoryId: number;
  authorId: number;
  status: 'draft' | 'published' | 'archived';
  views: number;
  createdAt: string;
  updatedAt: string;
  locale: string;
  meta: {
    keywords?: string[];
    ogImage?: string;
  };
}

export interface ArticleSearchResult {
  id: number;
  title: string;
  description: string;
  slug: string;
  categoryId: number;
  categoryName: string;
  locale: string;
  relevanceScore: number;
}

export interface ArticleFeedback {
  articleId: number;
  helpful: boolean;
  feedback?: string;
}

export interface ApiResponse<T> {
  data: T;
  meta?: {
    count?: number;
    currentPage?: number;
    totalPages?: number;
  };
}
