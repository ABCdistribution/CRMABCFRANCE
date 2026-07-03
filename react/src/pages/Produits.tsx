import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  Plus,
  Search,
  Filter,
  Package,
  Edit,
  Trash2,
  Eye,
  Download,
  Loader2
} from "lucide-react"
import { useProduits } from "../hooks/useProduits"
import { useState } from "react"

export default function Produits() {
  const { produits, stats, loading, error, searchProduits } = useProduits()
  const [searchTerm, setSearchTerm] = useState("")

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    searchProduits(searchTerm)
  }

  const statsCards = stats ? [
    {
      title: "Total produits",
      value: stats.total.toLocaleString(),
      description: "Références actives",
      icon: Package,
      color: "blue"
    },
    {
      title: "En stock",
      value: stats.enStock.toLocaleString(),
      description: `${Math.round((stats.enStock / stats.total) * 100)}% des produits`,
      icon: Package,
      color: "green"
    },
    {
      title: "Rupture stock",
      value: stats.ruptureStock.toLocaleString(),
      description: `${Math.round((stats.ruptureStock / stats.total) * 100)}% des produits`,
      icon: Package,
      color: "red"
    },
    {
      title: "Valeur stock",
      value: `€${stats.valeurStock.toLocaleString()}`,
      description: "Estimation totale",
      icon: Package,
      color: "purple"
    }
  ] : []

  const getColorClasses = (color: string) => {
    const colors = {
      blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6' },
      green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e' },
      red: { bg: '#fee2e2', icon: '#dc2626', border: '#ef4444' },
      purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7' }
    }
    return colors[color as keyof typeof colors] || colors.blue
  }

  return (
    <div className="page-container">
      {/* Header avec titre et boutons */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Gestion des Produits</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Gérez votre catalogue de produits</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Download style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Exporter
          </Button>
          <Button style={{ backgroundColor: '#2563eb', color: 'white' }}>
            <Plus style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Nouveau produit
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="stats-grid" style={{ marginBottom: '3rem' }}>
        {statsCards.map((stat, index) => {
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
          <form onSubmit={handleSearch} style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
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
                placeholder="Rechercher un produit..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                style={{ 
                  width: '100%', 
                  padding: '0.75rem 0.75rem 0.75rem 2.5rem', 
                  border: '1px solid #d1d5db', 
                  borderRadius: '0.375rem',
                  fontSize: '0.875rem'
                }}
              />
            </div>
            <Button type="submit" variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
              {loading ? (
                <Loader2 style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} className="animate-spin" />
              ) : (
                <Search style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
              )}
              Rechercher
            </Button>
            <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
              <Filter style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
              Filtres
            </Button>
          </form>
        </CardContent>
      </Card>

      {/* Tableau des produits */}
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
            Liste des produits
          </CardTitle>
        </CardHeader>
        <CardContent style={{ padding: 0 }}>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f9fafb' }}>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Produit
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Référence
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Prix
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Stock
                  </th>
                  <th style={{ padding: '0.75rem 1rem', textAlign: 'left', fontSize: '0.875rem', fontWeight: '600', color: '#374151', borderBottom: '1px solid #e5e7eb' }}>
                    Catégorie
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
                {loading ? (
                  <tr>
                    <td colSpan={7} style={{ padding: '2rem', textAlign: 'center' }}>
                      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
                        <Loader2 style={{ width: '1rem', height: '1rem' }} className="animate-spin" />
                        <span>Chargement des produits...</span>
                      </div>
                    </td>
                  </tr>
                ) : error ? (
                  <tr>
                    <td colSpan={7} style={{ padding: '2rem', textAlign: 'center', color: '#dc2626' }}>
                      <div>Erreur: {error}</div>
                    </td>
                  </tr>
                ) : produits.length === 0 ? (
                  <tr>
                    <td colSpan={7} style={{ padding: '2rem', textAlign: 'center', color: '#6b7280' }}>
                      <div>Aucun produit trouvé</div>
                    </td>
                  </tr>
                ) : (
                  produits.map((produit, index) => (
                    <tr key={produit.id} style={{ borderBottom: index < produits.length - 1 ? '1px solid #f3f4f6' : 'none' }}>
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
                            <Package style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                          </div>
                          <span style={{ fontWeight: '500' }}>{produit.libelle}</span>
                        </div>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {produit.id_as400}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#1f2937', fontWeight: '500' }}>
                        {produit.tarif ? `€${produit.tarif.toFixed(2)}` : 'N/A'}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#1f2937' }}>
                        {produit.stock ? produit.stock.qte || 'N/A' : 'N/A'}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem', color: '#6b7280' }}>
                        {produit.famille || 'N/A'}
                      </td>
                      <td style={{ padding: '0.75rem 1rem', fontSize: '0.875rem' }}>
                        <span style={{ 
                          padding: '0.25rem 0.5rem', 
                          borderRadius: '0.25rem', 
                          fontSize: '0.75rem',
                          fontWeight: '500',
                          backgroundColor: produit.actif ? '#dcfce7' : '#fee2e2',
                          color: produit.actif ? '#16a34a' : '#dc2626'
                        }}>
                          {produit.actif ? 'Actif' : 'Inactif'}
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
                  ))
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
