import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  Plus,
  Search,
  Filter,
  Languages,
  Edit,
  Trash2,
  Eye,
  Download,

  User,
  CheckCircle,

  AlertCircle
} from "lucide-react"

export default function Traductions() {
  const traductions = [
    {
      id: 1,
      cle: "welcome.message",
      texteOriginal: "Bienvenue dans notre application",
      langue: "Anglais",
      texteTraduit: "Welcome to our application",
      statut: "Traduit",
      traducteur: "Marie Martin",
      date: "16/01/2024",
      contexte: "Page d'accueil"
    },
    {
      id: 2,
      cle: "button.save",
      texteOriginal: "Enregistrer",
      langue: "Espagnol",
      texteTraduit: "Guardar",
      statut: "En cours",
      traducteur: "Pierre Durand",
      date: "15/01/2024",
      contexte: "Boutons d'action"
    },
    {
      id: 3,
      cle: "error.not.found",
      texteOriginal: "Page non trouvée",
      langue: "Allemand",
      texteTraduit: "",
      statut: "À traduire",
      traducteur: "Sophie Bernard",
      date: "14/01/2024",
      contexte: "Messages d'erreur"
    },
    {
      id: 4,
      cle: "menu.dashboard",
      texteOriginal: "Tableau de bord",
      langue: "Italien",
      texteTraduit: "Cruscotto",
      statut: "Validé",
      traducteur: "Jean Dupont",
      date: "13/01/2024",
      contexte: "Menu principal"
    }
  ]

  const stats = [
    {
      title: "Total traductions",
      value: "1,234",
      description: "+45 ce mois",
      icon: Languages,
      color: "blue"
    },
    {
      title: "À traduire",
      value: "89",
      description: "7% du total",
      icon: AlertCircle,
      color: "orange"
    },
    {
      title: "Traduites",
      value: "1,023",
      description: "83% du total",
      icon: CheckCircle,
      color: "green"
    },
    {
      title: "Langues supportées",
      value: "8",
      description: "Français, Anglais, Espagnol...",
      icon: Languages,
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
      'Traduit': { bg: '#dcfce7', color: '#16a34a' },
      'En cours': { bg: '#fef3c7', color: '#d97706' },
      'À traduire': { bg: '#fee2e2', color: '#dc2626' },
      'Validé': { bg: '#dbeafe', color: '#2563eb' }
    }
    return colors[statut as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  const getLangueColor = (langue: string) => {
    const colors = {
      'Anglais': { bg: '#dbeafe', color: '#2563eb' },
      'Espagnol': { bg: '#fef3c7', color: '#d97706' },
      'Allemand': { bg: '#f3f4f6', color: '#6b7280' },
      'Italien': { bg: '#dcfce7', color: '#16a34a' }
    }
    return colors[langue as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  return (
    <div className="page-container">
      {/* Header avec titre et boutons */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Traductions</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Gérez les traductions multilingues de l'application</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Download style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Exporter
          </Button>
          <Button style={{ backgroundColor: '#2563eb', color: 'white' }}>
            <Plus style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Nouvelle traduction
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
                placeholder="Rechercher une traduction..."
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

      {/* Tableau des traductions */}
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
            Liste des traductions
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f9fafb' }}>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Clé
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Texte original
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Langue
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Traduction
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Contexte
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Statut
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Traducteur
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'center', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                {traductions.map((traduction, index) => {
                  const statutColors = getStatutColor(traduction.statut)
                  const langueColors = getLangueColor(traduction.langue)
                  return (
                    <tr key={traduction.id} style={{ borderBottom: index < traductions.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
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
                            <Languages style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                          </div>
                          <span style={{ fontWeight: '500', fontFamily: 'monospace' }}>{traduction.cle}</span>
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {traduction.texteOriginal}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                        <span style={{ 
                          padding: '0.25rem 0.5rem', 
                          borderRadius: '0.25rem', 
                          fontSize: '0.75rem',
                          fontWeight: '500',
                          backgroundColor: langueColors.bg,
                          color: langueColors.color
                        }}>
                          {traduction.langue}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {traduction.texteTraduit || <span style={{ fontStyle: 'italic', color: '#9ca3af' }}>Non traduit</span>}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {traduction.contexte}
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
                          {traduction.statut}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <User style={{ width: '0.875rem', height: '0.875rem' }} />
                          {traduction.traducteur}
                        </div>
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
