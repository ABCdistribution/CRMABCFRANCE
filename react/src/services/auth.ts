// Service d'authentification pour communiquer avec l'API PHP
const API_BASE_URL = '/api';

interface LoginResponse {
  success: boolean;
  token?: string;
  user?: any;
  error?: string;
}

interface User {
  id: number;
  login: string;
  displayname: string;
  email?: string;
}

class AuthService {
  private token: string | null = null;
  private user: User | null = null;

  constructor() {
    // Récupérer le token depuis le localStorage au démarrage
    this.token = localStorage.getItem('auth_token');
    const userData = localStorage.getItem('user_data');
    if (userData) {
      this.user = JSON.parse(userData);
    }
  }

  private async makeRequest<T>(
    method: string,
    params: Record<string, any> = {}
  ): Promise<T> {
    try {
      // Encoder les paramètres comme le fait l'API PHP
      const encodedData = btoa(JSON.stringify({
        methode: method,
        ...params
      }));

      const response = await fetch(API_BASE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `d=${encodedData}`,
        credentials: 'include' // Pour les cookies de session
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      console.log('Réponse API brute:', result);
      
      // Décoder la réponse si elle est encodée
      if (result.d) {
        const decoded = JSON.parse(atob(result.d));
        console.log('Réponse API décodée:', decoded);
        if (decoded.error) {
          throw new Error(decoded.errorMsg || 'Erreur API');
        }
        return decoded;
      }

      return result;
    } catch (error) {
      console.error('Auth API Error:', error);
      throw error;
    }
  }

  async login(login: string, password: string): Promise<LoginResponse> {
    try {
      console.log('Tentative de connexion avec:', { login, password: '***' });
      const response = await this.makeRequest<LoginResponse>('auth', {
        login,
        pass: password
      });

      if (response.token && response.user) {
        this.token = response.token;
        this.user = response.user;
        
        // Sauvegarder dans le localStorage
        localStorage.setItem('auth_token', response.token);
        localStorage.setItem('user_data', JSON.stringify(response.user));
        
        return { success: true, token: response.token, user: response.user };
      } else {
        throw new Error(response.error || 'Erreur de connexion');
      }
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  async logout(): Promise<void> {
    this.token = null;
    this.user = null;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
  }

  async checkTokenValidity(): Promise<boolean> {
    if (!this.token) return false;

    try {
      const response = await this.makeRequest<{expire: boolean, user: any}>('checkTokenValidity', {
        token: this.token
      });

      if (!response.expire && response.user) {
        this.user = response.user;
        localStorage.setItem('user_data', JSON.stringify(response.user));
        return true;
      } else {
        await this.logout();
        return false;
      }
    } catch (error) {
      console.error('Token validation error:', error);
      await this.logout();
      return false;
    }
  }

  isAuthenticated(): boolean {
    return this.token !== null;
  }

  getToken(): string | null {
    return this.token;
  }

  getUser(): User | null {
    return this.user;
  }

  // Méthode pour les requêtes authentifiées
  async authenticatedRequest<T>(
    method: string,
    params: Record<string, any> = {}
  ): Promise<T> {
    if (!this.token) {
      throw new Error('Non authentifié');
    }

    return this.makeRequest<T>(method, {
      token: this.token,
      ...params
    });
  }
}

export const authService = new AuthService();
export default authService;
