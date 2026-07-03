import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { useAuth } from '../hooks/useAuthContext';
import { Loader2, User, Lock } from 'lucide-react';

export default function Login() {
  const [login, setLogin] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login: loginUser, error } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!login.trim() || !password.trim()) return;

    setIsLoading(true);
    try {
      await loginUser(login, password);
    } catch (err) {
      // L'erreur est gérée par le hook useAuth
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div style={{
      minHeight: '100vh',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: '#f8fafc',
      padding: '1rem'
    }}>
      <Card style={{
        width: '100%',
        maxWidth: '400px',
        border: '2px solid #e5e7eb',
        boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        borderRadius: '0.75rem'
      }}>
        <CardHeader style={{ textAlign: 'center', padding: '2rem 2rem 1rem 2rem' }}>
          <CardTitle style={{ 
            fontSize: '1.5rem', 
            fontWeight: 'bold', 
            color: '#1f2937',
            margin: 0
          }}>
            Connexion CRM
          </CardTitle>
          <p style={{ 
            color: '#6b7280', 
            margin: '0.5rem 0 0 0',
            fontSize: '0.875rem'
          }}>
            Accédez à votre espace de travail
          </p>
        </CardHeader>
        
        <CardContent style={{ padding: '1rem 2rem 2rem 2rem' }}>
          <form onSubmit={handleSubmit}>
            <div style={{ marginBottom: '1.5rem' }}>
              <label style={{
                display: 'block',
                fontSize: '0.875rem',
                fontWeight: '500',
                color: '#374151',
                marginBottom: '0.5rem'
              }}>
                Nom d'utilisateur
              </label>
              <div style={{ position: 'relative' }}>
                <User style={{
                  position: 'absolute',
                  left: '0.75rem',
                  top: '50%',
                  transform: 'translateY(-50%)',
                  width: '1rem',
                  height: '1rem',
                  color: '#6b7280'
                }} />
                <input
                  type="text"
                  value={login}
                  onChange={(e) => setLogin(e.target.value)}
                  placeholder="Votre nom d'utilisateur"
                  style={{
                    width: '100%',
                    padding: '0.75rem 0.75rem 0.75rem 2.5rem',
                    border: '1px solid #d1d5db',
                    borderRadius: '0.375rem',
                    fontSize: '0.875rem',
                    outline: 'none',
                    transition: 'border-color 0.2s'
                  }}
                  onFocus={(e) => e.target.style.borderColor = '#3b82f6'}
                  onBlur={(e) => e.target.style.borderColor = '#d1d5db'}
                />
              </div>
            </div>

            <div style={{ marginBottom: '1.5rem' }}>
              <label style={{
                display: 'block',
                fontSize: '0.875rem',
                fontWeight: '500',
                color: '#374151',
                marginBottom: '0.5rem'
              }}>
                Mot de passe
              </label>
              <div style={{ position: 'relative' }}>
                <Lock style={{
                  position: 'absolute',
                  left: '0.75rem',
                  top: '50%',
                  transform: 'translateY(-50%)',
                  width: '1rem',
                  height: '1rem',
                  color: '#6b7280'
                }} />
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Votre mot de passe"
                  style={{
                    width: '100%',
                    padding: '0.75rem 0.75rem 0.75rem 2.5rem',
                    border: '1px solid #d1d5db',
                    borderRadius: '0.375rem',
                    fontSize: '0.875rem',
                    outline: 'none',
                    transition: 'border-color 0.2s'
                  }}
                  onFocus={(e) => e.target.style.borderColor = '#3b82f6'}
                  onBlur={(e) => e.target.style.borderColor = '#d1d5db'}
                />
              </div>
            </div>

            {error && (
              <div style={{
                padding: '0.75rem',
                backgroundColor: '#fee2e2',
                border: '1px solid #fecaca',
                borderRadius: '0.375rem',
                marginBottom: '1rem'
              }}>
                <p style={{
                  color: '#dc2626',
                  fontSize: '0.875rem',
                  margin: 0
                }}>
                  {error}
                </p>
              </div>
            )}

            <Button
              type="submit"
              disabled={isLoading || !login.trim() || !password.trim()}
              style={{
                width: '100%',
                backgroundColor: '#2563eb',
                color: 'white',
                padding: '0.75rem',
                borderRadius: '0.375rem',
                border: 'none',
                fontSize: '0.875rem',
                fontWeight: '500',
                cursor: isLoading ? 'not-allowed' : 'pointer',
                opacity: isLoading ? 0.7 : 1,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.5rem'
              }}
            >
              {isLoading ? (
                <>
                  <Loader2 style={{ width: '1rem', height: '1rem' }} className="animate-spin" />
                  Connexion...
                </>
              ) : (
                'Se connecter'
              )}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
