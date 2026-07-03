// Service API pour communiquer avec le backend PHP
import { authService } from './auth';

const API_BASE_URL = '/api'; // URL de base de votre API PHP

class ApiService {
  private async makeRequest<T>(
    method: string,
    params: Record<string, any> = {}
  ): Promise<T> {
    try {
      // Vérifier l'authentification
      if (!authService.isAuthenticated()) {
        throw new Error('Non authentifié');
      }

      // Encoder les paramètres comme le fait l'API PHP
      const encodedData = btoa(JSON.stringify({
        methode: method,
        token: authService.getToken(),
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
      
      // Décoder la réponse si elle est encodée
      if (result.d) {
        const decoded = JSON.parse(atob(result.d));
        if (decoded.error) {
          throw new Error(decoded.errorMsg || 'Erreur API');
        }
        return decoded;
      }

      if (!result.success && result.error) {
        throw new Error(result.error);
      }

      return result.data || result;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  // Méthodes pour les produits
  async getProduitsStats() {
    return this.makeRequest('ref::getProduitsStats');
  }

  async getLastProduits(count: number = 5) {
    return this.makeRequest('ref::getLastProduits', { count });
  }

  async searchProduit(search: string, count: number = 30) {
    return this.makeRequest('ref::searchProduit', { search, count });
  }

  async getProduit(id: number) {
    return this.makeRequest('ref::getProduit', { id });
  }

  async countTotalProduit() {
    return this.makeRequest('ref::countTotalProduit');
  }

  // Méthodes pour les clients
  async getClientsStats() {
    return this.makeRequest('ref::getClientsStats');
  }

  async getLastClients(count: number = 5) {
    return this.makeRequest('ref::getLastClients', { count });
  }

  // Méthodes pour les commandes
  async getCommandesStats() {
    return this.makeRequest('commande::getCommandesStats');
  }

  async getLastCommandes(count: number = 5) {
    return this.makeRequest('commande::getLastCommandes', { count });
  }
}

export const apiService = new ApiService();
export default apiService;
