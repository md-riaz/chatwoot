import ky from 'ky'

export const api = ky.create({
  prefixUrl: (import.meta.env.VITE_API_URL as string) || '/',
  hooks: {
    beforeRequest: [request => {
      try {
        const token = localStorage.getItem('token');
        if (token) request.headers.set('Authorization', `Bearer ${token}`);
      } catch (e) {
        // ignore in non-browser environments
      }
    }]
  }
});

export default api;
