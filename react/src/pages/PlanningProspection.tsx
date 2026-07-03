import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  Plus,
  Search,
  Filter,
  Calendar,
  Edit,
  Trash2,
  Eye,
  Download,

  User,
  CheckCircle,
  Target,

} from "lucide-react"

export default function PlanningProspection() {
  const tachesProspection = [
    {
      id: 1,
      titre: "Appel prospect ABC",
      commercial: "Marie Martin",
      prospect: "Entreprise ABC",
      date: "16/01/2024",
      heure: "14:30",
      duree: "30min",
      statut: "Planifiée",
      type: "Appel téléphonique",
      priorite: "Haute",
      description: "Relance pour présentation produits"
    },
    {
      id: 2,
      titre: "Email suivi DEF",
      commercial: "Pierre Durand",
      prospect: "Société DEF",
      date: "17/01/2024",
      heure: "10:00",
      duree: "15min",
      statut: "Réalisée",
      type: "Email",
      priorite: "Moyenne",
      description: "Envoi documentation technique"
    },
    {
      id: 3,
      titre: "Préparation GHI",
      commercial: "Sophie Bernard",
      prospect: "Groupe GHI",
      date: "18/01/2024",
      heure: "16:00",
      duree: "1h",
      statut: "En cours",
      type: "Préparation",
      priorite: "Haute",
      description: "Préparation présentation personnalisée"
    },
    {
      id: 4,
      titre: "Rapport JKL",
      commercial: "Jean Dupont",
      prospect: "Corporation JKL",
      date: "19/01/2024",
      heure: "09:30",
      duree: "45min",
      statut: "Annulée",
      type: "Rapport",
      priorite: "Basse",
      description: "Rédaction rapport de visite"
    }
  ]

  const stats = [
    {
      title: "Total tâches",
      value: "156",
      description: "+12% cette semaine",
      icon: Target,
      color: "blue"
    },
    {
      title: "Planifiées",
      value: "67",
      description: "43% du total",
      icon: Target,
      color: "orange"
    },
    {
      title: "Réalisées",
      value: "72",
      description: "46% du total",
      icon: CheckCircle,
      color: "green"
    },
    {
      title: "Taux de réalisation",
      value: "78%",
      description: "+5% vs semaine dernière",
      icon: Target,
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
      'Planifiée': { bg: '#fef3c7', color: '#d97706' },
      'Réalisée': { bg: '#dcfce7', color: '#16a34a' },
      'En cours': { bg: '#dbeafe', color: '#2563eb' },
      'Annulée': { bg: '#fee2e2', color: '#dc2626' }
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

  const getTypeColor = (type: string) => {
    const colors = {
      'Appel téléphonique': { bg: '#dbeafe', color: '#2563eb' },
      'Email': { bg: '#dcfce7', color: '#16a34a' },
      'Préparation': { bg: '#fef3c7', color: '#d97706' },
      'Rapport': { bg: '#f3e8ff', color: '#9333ea' }
    }
    return colors[type as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  return (
    <div className="page-container">
      {/* Header avec titre et boutons */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Tâches de prospection</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Planifiez et gérez les tâches de prospection</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Download style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Exporter
          </Button>
          <Button style={{ backgroundColor: '#2563eb', color: 'white' }}>
            <Plus style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Nouvelle tâche
          </Button>
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
                placeholder="Rechercher une tâche..."
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

      {/* Tableau des tâches */}
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
            Liste des tâches de prospection
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f9fafb' }}>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Tâche
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Commercial
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Prospect
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
                {tachesProspection.map((tache, index) => {
                  const statutColors = getStatutColor(tache.statut)
                  const prioriteColors = getPrioriteColor(tache.priorite)
                  const typeColors = getTypeColor(tache.type)
                  return (
                    <tr key={tache.id} style={{ borderBottom: index < tachesProspection.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#1f2937' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                          <div style={{ 
                            width: '2rem', 
                            height: '2rem', 
                            backgroundColor: '#f3f4f6', 
                            borderRadius: '0.375rem',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                          }}>
                            <Target style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                          </div>
                          <div>
                            <div style={{ fontWeight: '500' }}>{tache.titre}</div>
                            <div style={{ fontSize: '0.75rem', color: '#6b7280' }}>{tache.description}</div>
                          </div>
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <User style={{ width: '0.875rem', height: '0.875rem' }} />
                          {tache.commercial}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {tache.prospect}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <Calendar style={{ width: '0.875rem', height: '0.875rem' }} />
                          {tache.date} {tache.heure}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                        <span style={{ 
                          padding: '0.25rem 0.5rem', 
                          borderRadius: '0.25rem', 
                          fontSize: '0.75rem',
                          fontWeight: '500',
                          backgroundColor: typeColors.bg,
                          color: typeColors.color
                        }}>
                          {tache.type}
                        </span>
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
                          {tache.priorite}
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
                          {tache.statut}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', textAlign: 'center' }}>
                        <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'center' }}>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#6b7280' }}>
                            <Eye style={{ width: '1rem', height: '1rem' }} />
                          </Button>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#6b7280' }}>
                            <Edit style={{ width: '1rem', height: '1rem' }} />
                          </Button>
                          <Button variant="ghost" size="sm" style={{ padding: '0.25rem', color: '#dc2626' }}>
                            <Trash2 style={{ width: '1rem', height: '1rem' }} />
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
