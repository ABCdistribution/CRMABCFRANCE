import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  Plus,
  Search,
  Filter,
  MapPin,
  Edit,
  Trash2,
  Eye,
  Download,
  Calendar,
  User,

  CheckCircle,
  Target
} from "lucide-react"

export default function Prospections() {
  const prospections = [
    {
      id: 1,
      prospect: "Entreprise ABC",
      commercial: "Marie Martin",
      date: "16/01/2024",
      heure: "14:30",
      duree: "2h",
      statut: "Planifiée",
      type: "Premier contact",
      adresse: "123 Rue de la Paix, 75001 Paris",
      objectif: "Présentation produits"
    },
    {
      id: 2,
      prospect: "Société DEF",
      commercial: "Pierre Durand",
      date: "17/01/2024",
      heure: "10:00",
      duree: "1h30",
      statut: "Réalisée",
      type: "Relance",
      adresse: "45 Avenue des Champs, 69000 Lyon",
      objectif: "Négociation prix"
    },
    {
      id: 3,
      prospect: "Groupe GHI",
      commercial: "Sophie Bernard",
      date: "18/01/2024",
      heure: "16:00",
      duree: "3h",
      statut: "En cours",
      type: "Démonstration",
      adresse: "78 Boulevard du Port, 13000 Marseille",
      objectif: "Test produit"
    },
    {
      id: 4,
      prospect: "Corporation JKL",
      commercial: "Jean Dupont",
      date: "19/01/2024",
      heure: "09:30",
      duree: "2h",
      statut: "Annulée",
      type: "Présentation",
      adresse: "12 Place du Capitole, 31000 Toulouse",
      objectif: "Signature contrat"
    }
  ]

  const stats = [
    {
      title: "Total prospections",
      value: "67",
      description: "+18% ce mois",
      icon: Target,
      color: "blue"
    },
    {
      title: "Planifiées",
      value: "23",
      description: "34% du total",
      icon: Target,
      color: "orange"
    },
    {
      title: "Réalisées",
      value: "34",
      description: "51% du total",
      icon: CheckCircle,
      color: "green"
    },
    {
      title: "Taux de conversion",
      value: "42%",
      description: "+8% vs mois dernier",
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

  const getTypeColor = (type: string) => {
    const colors = {
      'Premier contact': { bg: '#dbeafe', color: '#2563eb' },
      'Relance': { bg: '#fef3c7', color: '#d97706' },
      'Démonstration': { bg: '#dcfce7', color: '#16a34a' },
      'Présentation': { bg: '#f3e8ff', color: '#9333ea' }
    }
    return colors[type as keyof typeof colors] || { bg: '#f3f4f6', color: '#6b7280' }
  }

  return (
    <div className="page-container">
      {/* Header avec titre et boutons */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Visites de prospection</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Gérez les visites de prospection commerciale</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Download style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Exporter
          </Button>
          <Button style={{ backgroundColor: '#2563eb', color: 'white' }}>
            <Plus style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Nouvelle prospection
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
                placeholder="Rechercher une prospection..."
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

      {/* Tableau des prospections */}
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
            Liste des prospections
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f9fafb' }}>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Prospect
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Commercial
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Date & Heure
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Type
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Objectif
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Adresse
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
                {prospections.map((prospection, index) => {
                  const statutColors = getStatutColor(prospection.statut)
                  const typeColors = getTypeColor(prospection.type)
                  return (
                    <tr key={prospection.id} style={{ borderBottom: index < prospections.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
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
                          <span style={{ fontWeight: '500' }}>{prospection.prospect}</span>
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <User style={{ width: '0.875rem', height: '0.875rem' }} />
                          {prospection.commercial}
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <Calendar style={{ width: '0.875rem', height: '0.875rem' }} />
                          {prospection.date} {prospection.heure}
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
                          {prospection.type}
                        </span>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {prospection.objectif}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                          <MapPin style={{ width: '0.875rem', height: '0.875rem' }} />
                          {prospection.adresse}
                        </div>
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
                          {prospection.statut}
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
