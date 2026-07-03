import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  CheckCircle,
  XCircle,
  Search,
  Filter,
  Calendar,
  Eye,
  Download,
  Clock,
  User,
  MapPin,
  AlertCircle
} from "lucide-react"

export default function ValidationPlanning() {
  const validations = [
    {
      id: 1,
      titre: "Visite ABC Paris",
      promoteur: "Jean Dupont",
      client: "Magasin ABC Paris",
      date: "16/01/2024",
      heure: "14:30",
      duree: "2h",
      statut: "En attente",
      type: "Visite commerciale",
      priorite: "Haute",
      demande: "15/01/2024"
    },
    {
      id: 2,
      titre: "Formation DEF Lyon",
      promoteur: "Marie Martin",
      client: "Boutique DEF Lyon",
      date: "17/01/2024",
      heure: "10:00",
      duree: "3h",
      statut: "Approuvée",
      type: "Formation",
      priorite: "Moyenne",
      demande: "14/01/2024"
    },
    {
      id: 3,
      titre: "Suivi GHI Marseille",
      promoteur: "Pierre Durand",
      client: "Store GHI Marseille",
      date: "18/01/2024",
      heure: "16:00",
      duree: "1h30",
      statut: "Rejetée",
      type: "Suivi client",
      priorite: "Basse",
      demande: "13/01/2024"
    },
    {
      id: 4,
      titre: "Rendez-vous JKL Toulouse",
      promoteur: "Sophie Bernard",
      client: "Shop JKL Toulouse",
      date: "19/01/2024",
      heure: "09:30",
      duree: "2h",
      statut: "En attente",
      type: "Rendez-vous",
      priorite: "Haute",
      demande: "12/01/2024"
    }
  ]

  const stats = [
    {
      title: "Total demandes",
      value: "67",
      description: "+12% cette semaine",
      icon: Calendar,
      color: "blue"
    },
    {
      title: "En attente",
      value: "23",
      description: "34% du total",
      icon: AlertCircle,
      color: "orange"
    },
    {
      title: "Approuvées",
      value: "32",
      description: "48% du total",
      icon: CheckCircle,
      color: "green"
    },
    {
      title: "Taux d'approbation",
      value: "78%",
      description: "+5% vs semaine dernière",
      icon: Calendar,
      color: "purple"
    }
  ]

  const getColorClasses = (color: string) => {
    const colors = {
      blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6' },
      green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e' },
      red: { bg: '#fee2e2', icon: '#dc2626', border: '#ef4444' },
      purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7' },
      orange: { bg: '#fed7aa', icon: '#ea580c', border: '#f97316' }
    }
    return colors[color as keyof typeof colors] || colors.blue
  }

  const getStatutColor = (statut: string) => {
    const colors = {
      'En attente': { bg: '#fef3c7', color: '#d97706' },
      'Approuvée': { bg: '#dcfce7', color: '#16a34a' },
      'Rejetée': { bg: '#fee2e2', color: '#dc2626' }
    }
    return colors[statut as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  const getPrioriteColor = (priorite: string) => {
    const colors = {
      'Haute': { bg: '#fee2e2', color: '#dc2626' },
      'Moyenne': { bg: '#fef3c7', color: '#d97706' },
      'Basse': { bg: '#dcfce7', color: '#16a34a' }
    }
    return colors[priorite as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  return (
    <div className="page-container">
      {/* Header avec titre et boutons */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Validation du Planning</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Validez et approuvez les demandes de planning</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Download style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Exporter
          </Button>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <Button style={{ backgroundColor: '#16a34a', color: 'white' }}>
              <CheckCircle style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
              Approuver sélection
            </Button>
            <Button variant="outline" style={{ borderColor: '#dc2626', color: '#dc2626' }}>
              <XCircle style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
              Rejeter sélection
            </Button>
          </div>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="stats-grid" style={{ marginBottom: '3rem' }}>
        {stats.map((stat, index) => {
          const Icon = stat.icon
          const colors = getColorClasses(stat.color)
          
          return (
            <Card key={index} style={{ 
              border: '2px solid #e5e7eb',
              backgroundColor: 'white',
              boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
              borderRadius: '0.5rem'
            }}>
              <CardContent style={{ padding: '1.5rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', marginBottom: '1rem' }}>
                  <div style={{ 
                    padding: '0.75rem', 
                    borderRadius: '0.5rem', 
                    backgroundColor: colors.bg 
                  }}>
                    <Icon style={{ width: '1.5rem', height: '1.5rem', color: colors.icon }} />
                  </div>
                </div>
                <div style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', marginBottom: '0.5rem' }}>
                  {stat.value}
                </div>
                <p style={{ fontSize: '0.875rem', fontWeight: '600', color: '#1f2937', margin: '0 0 0.25rem 0' }}>
                  {stat.title}
                </p>
                <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                  {stat.description}
                </p>
              </CardContent>
            </Card>
          )
        })}
      </div>

      {/* Barre de recherche et filtres */}
      <Card style={{ 
        border: '2px solid #e5e7eb',
        backgroundColor: 'white',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        borderRadius: '0.5rem',
        marginBottom: '2rem'
      }}>
        <CardContent style={{ padding: '1.5rem' }}>
          <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
            <div style={{ position: 'relative', flex: 1 }}>
              <Search style={{ 
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
                placeholder="Rechercher une demande..."
                style={{ 
                  width: '100%', 
                  padding: '0.75rem 0.75rem 0.75rem 2.5rem', 
                  border: '1px solid #d1d5db', 
                  borderRadius: '0.375rem',
                  fontSize: '0.875rem'
                }}
              />
            </div>
            <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
              <Filter style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
              Filtres
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Tableau des validations */}
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
            Demandes de validation
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f9fafb' }}>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    <input type="checkbox" style={{ marginRight: '0.5rem' }} />
                    Activité
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Promoteur
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Client
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Date & Heure
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Type
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Priorité
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Statut
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'center', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                {validations.map((validation, index) => {
                  const statutColors = getStatutColor(validation.statut)
                  const prioriteColors = getPrioriteColor(validation.priorite)
                  return (
                    <tr key={validation.id} style={{ borderBottom: index < validations.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#1f2937' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                          <input type="checkbox" style={{ marginRight: '0.5rem' }} />
                          <div style={{ 
                            width: '2rem', 
                            height: '2rem', 
                            backgroundColor: '#f3f4f6', 
                            borderRadius: '0.375rem',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                          }}>
                            <Calendar style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                          </div>
                          <div>
                            <div style={{ fontWeight: '500' }}>{validation.titre}</div>
                            <div style={{ fontSize: '0.75rem', color: '#6b7280' }}>Demandé le {validation.demande}</div>
                          </div>
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <User style={{ width: '0.875rem', height: '0.875rem' }} />
                          {validation.promoteur}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <MapPin style={{ width: '0.875rem', height: '0.875rem' }} />
                          {validation.client}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <Clock style={{ width: '0.875rem', height: '0.875rem' }} />
                          {validation.date} {validation.heure}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {validation.type}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                        <span style={{ 
                          padding: '0.25rem 0.5rem', 
                          borderRadius: '0.25rem', 
                          fontSize: '0.75rem',
                          fontWeight: '500',
                          backgroundColor: prioriteColors.bg,
                          color: prioriteColors.color
                        }}>
                          {validation.priorite}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                        <span style={{ 
                          padding: '0.25rem 0.5rem', 
                          borderRadius: '0.25rem', 
                          fontSize: '0.75rem',
                          fontWeight: '500',
                          backgroundColor: statutColors.bg,
                          color: statutColors.color
                        }}>
                          {validation.statut}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', textAlign: 'center' }}>
                        <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'center' }}>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#6b7280' }}>
                            <Eye style={{ width: '1rem', height: '1rem' }} />
                          </Button>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#16a34a' }}>
                            <CheckCircle style={{ width: '1rem', height: '1rem' }} />
                          </Button>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#dc2626' }}>
                            <XCircle style={{ width: '1rem', height: '1rem' }} />
                          </Button>
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
