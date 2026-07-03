import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  Plus,
  Search,
  Filter,
  Users,
  Edit,
  Trash2,
  Eye,
  Download,
  MapPin,
  Phone,
  Mail,
  Loader2
} from "lucide-react"
import { useState, useEffect } from "react"

interface Client {
  id: number;
  id_as400: string;
  enseigne: string;
  raison_sociale?: string;
  adresse: string;
  code_postal: string;
  ville: string;
  pays?: string;
  actif: number;
  date_creation: string;
}

export default function Clients() {
  const [clients, setClients] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchClients();
  }, []);

  const fetchClients = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('authToken');
      
      if (!token) {
        setError('Token d\'authentification manquant');
        return;
      }

      const response = await fetch('http://localhost:3001/api/clients', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error('Erreur lors de la récupération des clients');
      }

      const data = await response.json();
      
      if (data.success) {
        setClients(data.data);
      } else {
        setError(data.errorMsg || 'Erreur inconnue');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur de connexion');
    } finally {
      setLoading(false);
    }
  };

  const stats = [
    {
      title: "Total clients",
      value: clients.length.toString(),
      description: "Clients assignés",
      icon: Users,
      color: "blue"
    },
    {
      title: "Clients actifs",
      value: clients.filter(c => c.actif === 1).length.toString(),
      description: `${clients.length > 0 ? Math.round((clients.filter(c => c.actif === 1).length / clients.length) * 100) : 0}% du total`,
      icon: Users,
      color: "green"
    },
    {
      title: "Nouveaux clients",
      value: clients.filter(c => {
        const createdDate = new Date(c.date_creation);
        const now = new Date();
        const diffTime = Math.abs(now.getTime() - createdDate.getTime());
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays <= 30;
      }).length.toString(),
      description: "Ce mois",
      icon: Users,
      color: "purple"
    },
    {
      title: "Villes",
      value: [...new Set(clients.map(c => c.ville))].length.toString(),
      description: "Villes différentes",
      icon: Users,
      color: "orange"
    }
  ]

  const getColorClasses = (color: string) => {
    const colors = {
      blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6' },
      green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e' },
      purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7' },
      orange: { bg: '#fed7aa', icon: '#ea580c', border: '#f97316' }
    };
    return colors[color as keyof typeof colors] || colors.blue;
  };

  return (
    <div style={{ padding: '1.5rem', backgroundColor: '#f8fafc', minHeight: '100vh' }}>
      {/* En-tête */}
      <div style={{ marginBottom: '2rem' }}>
        <h1 style={{ fontSize: '2rem', fontWeight: '700', color: '#1f2937', margin: '0 0 0.5rem 0' }}>
          Gestion des clients
        </h1>
        <p style={{ color: '#6b7280', fontSize: '1.125rem', margin: 0 }}>
          Gérez vos clients et leurs informations
        </p>
      </div>

      {/* Statistiques */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '1.5rem', marginBottom: '2rem' }}>
        {stats.map((stat, index) => {
          const colors = getColorClasses(stat.color);
          const IconComponent = stat.icon;
          
          return (
            <Card key={index} style={{ 
              border: `2px solid ${colors.border}`,
              backgroundColor: 'white',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
              borderRadius: '0.75rem'
            }}>
              <CardContent style={{ padding: '1.5rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                  <div>
                    <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#6b7280', margin: '0 0 0.5rem 0' }}>
                      {stat.title}
                    </p>
                    <p style={{ fontSize: '2rem', fontWeight: '700', color: '#1f2937', margin: '0 0 0.25rem 0' }}>
                      {stat.value}
                    </p>
                    <p style={{ fontSize: '0.875rem', color: '#6b7280', margin: 0 }}>
                      {stat.description}
                    </p>
                  </div>
                  <div style={{ 
                    width: '3rem', 
                    height: '3rem', 
                    backgroundColor: colors.bg, 
                    borderRadius: '0.75rem',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                  }}>
                    <IconComponent style={{ width: '1.5rem', height: '1.5rem', color: colors.icon }} />
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Actions */}
      <Card style={{ 
        border: '2px solid #e5e7eb',
        backgroundColor: 'white',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        borderRadius: '0.5rem',
        marginBottom: '1.5rem'
      }}>
        <CardContent style={{ padding: '1.5rem' }}>
          <div style={{ display: 'flex', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
            <Button style={{ backgroundColor: '#3b82f6', color: 'white' }}>
              <Plus style={{ width: '1rem', height: '1rem', marginRight: '0.5rem' }} />
              Nouveau client
            </Button>
            <Button variant="outline">
              <Search style={{ width: '1rem', height: '1rem', marginRight: '0.5rem' }} />
              Rechercher
            </Button>
            <Button variant="outline">
              <Filter style={{ width: '1rem', height: '1rem', marginRight: '0.5rem' }} />
              Filtrer
            </Button>
            <Button variant="outline">
              <Download style={{ width: '1rem', height: '1rem', marginRight: '0.5rem' }} />
              Exporter
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Tableau des clients */}
      <Card style={{ 
        border: '2px solid #e5e7eb',
        backgroundColor: 'white',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        borderRadius: '0.5rem'
      }}>
        <CardHeader style={{ 
          borderBottom: '1px solid #e5e7eb', 
          backgroundColor: '#f9fafb',
          padding: '1rem 1.5rem'
        }}>
          <CardTitle style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
            Liste des clients
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          {loading ? (
            <div style={{ padding: '2rem', textAlign: 'center' }}>
              <Loader2 className="h-8 w-8 animate-spin mx-auto mb-4" />
              <p>Chargement des clients...</p>
            </div>
          ) : error ? (
            <div style={{ padding: '2rem', textAlign: 'center' }}>
              <p style={{ color: '#ef4444', marginBottom: '1rem' }}>Erreur: {error}</p>
              <Button onClick={fetchClients} variant="outline">
                Réessayer
              </Button>
            </div>
          ) : (
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                  <tr style={{ backgroundColor: '#f9fafb' }}>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Client
                    </th>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Adresse
                    </th>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Ville
                    </th>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Statut
                    </th>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Code
                    </th>
                    <th style={{ padding: '0.75rem 1rem', textAlign: 'center', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {clients.length === 0 ? (
                    <tr>
                      <td colSpan={6} style={{ padding: '2rem', textAlign: 'center', color: '#6b7280' }}>
                        Aucun client trouvé
                      </td>
                    </tr>
                  ) : (
                    clients.map((client, index) => (
                      <tr key={client.id} style={{ borderBottom: index < clients.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
                        <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#111827' }}>
                          <div>
                            <div style={{ fontWeight: '500', color: '#111827' }}>
                              {client.enseigne}
                            </div>
                            {client.raison_sociale && (
                              <div style={{ fontSize: '0.75rem', color: '#6b7280' }}>
                                {client.raison_sociale}
                              </div>
                            )}
                          </div>
                        </td>
                        <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                            <MapPin className="h-4 w-4" />
                            <span>
                              {client.adresse}
                              {client.code_postal && `, ${client.code_postal}`}
                            </span>
                          </div>
                        </td>
                        <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                          {client.ville}
                        </td>
                        <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                          <span style={{
                            padding: '0.25rem 0.5rem',
                            borderRadius: '0.375rem',
                            fontSize: '0.75rem',
                            fontWeight: '500',
                            backgroundColor: client.actif === 1 ? '#dcfce7' : '#fef2f2',
                            color: client.actif === 1 ? '#166534' : '#991b1b'
                          }}>
                            {client.actif === 1 ? 'Actif' : 'Inactif'}
                          </span>
                        </td>
                        <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                          {client.id_as400}
                        </td>
                        <td style={{ padding: '0.75rem 1rem', textAlign: 'center' }}>
                          <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'center' }}>
                            <Button size="sm" variant="outline">
                              <Eye className="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="outline">
                              <Edit className="h-4 w-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}





