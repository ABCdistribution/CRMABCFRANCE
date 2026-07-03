import { useState, useEffect } from 'react';
import { authService } from '../services/auth';

interface User {
  id: number;
  login: string;
  displayname: string;
  email?: string;
}

export const useAuthState = () => {
  const [user, setUser] = useState<User | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const initAuth = async () => {
      try {
        setIsLoading(true);
        setError(null);
        
        if (authService.isAuthenticated()) {
          const isValid = await authService.checkTokenValidity();
          if (isValid) {
            setUser(authService.getUser());
            setIsAuthenticated(true);
          } else {
            setUser(null);
            setIsAuthenticated(false);
          }
        } else {
          setUser(null);
          setIsAuthenticated(false);
        }
      } catch (err) {
        console.error('Auth initialization error:', err);
        setError(err instanceof Error ? err.message : 'Erreur d\'authentification');
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setIsLoading(false);
      }
    };

    initAuth();
  }, []);

  const login = async (login: string, password: string) => {
    try {
      setIsLoading(true);
      setError(null);
      
      const response = await authService.login(login, password);
      
      if (response.success && response.user) {
        setUser(response.user);
        setIsAuthenticated(true);
      } else {
        throw new Error(response.error || 'Erreur de connexion');
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erreur de connexion';
      setError(errorMessage);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
      setUser(null);
      setIsAuthenticated(false);
      setError(null);
    } catch (err) {
      console.error('Logout error:', err);
    }
  };

  return {
    user,
    isAuthenticated,
    isLoading,
    login,
    logout,
    error
  };
};
