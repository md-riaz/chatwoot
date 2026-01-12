import type { PageLoad } from './$types';

export const load: PageLoad = async ({ url }) => {
  const config = url.searchParams.get('config') || 'general';
  
  return {
    config
  };
};