import { useState, useEffect } from 'react';

export interface Produit {
  id: number;
  id_as400: string;
  libelle: string;
  gencode?: string;
  famille?: string;
  ss_famille?: string;
  marque?: string;
  tarif?: number;
  stock?: any;
  actif: number;
  date_creation: string;
  date_modification: string;
}

export interface ProduitsStats {
  total: number;
  enStock: number;
  ruptureStock: number;
  valeurStock: number;
}

export const useProduits = () => {
  const [produits, setProduits] = useState<Produit[]>([]);
  const [stats, setStats] = useState<ProduitsStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchProduits = async (count: number = 10) => {
    setLoading(true);
    setError(null);
    try {
      // Données simulées en attendant que l'API soit configurée
      const mockProduits: Produit[] = [
        {
          id: 1,
          id_as400: "ABC001",
          libelle: "Crème hydratante visage",
          gencode: "1234567890123",
          famille: "Soins visage",
          ss_famille: "Hydratants",
          marque: "ABC Cosmétique",
          tarif: 24.99,
          stock: { qte: 150 },
          actif: 1,
          date_creation: "2024-01-15",
          date_modification: "2024-01-20"
        },
        {
          id: 2,
          id_as400: "ABC002",
          libelle: "Sérum anti-âge",
          gencode: "1234567890124",
          famille: "Soins visage",
          ss_famille: "Anti-âge",
          marque: "ABC Cosmétique",
          tarif: 45.99,
          stock: { qte: 75 },
          actif: 1,
          date_creation: "2024-01-16",
          date_modification: "2024-01-21"
        },
        {
          id: 3,
          id_as400: "ABC003",
          libelle: "Masque purifiant",
          gencode: "1234567890125",
          famille: "Soins visage",
          ss_famille: "Masques",
          marque: "ABC Cosmétique",
          tarif: 18.99,
          stock: { qte: 0 },
          actif: 1,
          date_creation: "2024-01-17",
          date_modification: "2024-01-22"
        },
        {
          id: 4,
          id_as400: "ABC004",
          libelle: "Gel douche relaxant",
          gencode: "1234567890126",
          famille: "Corps",
          ss_famille: "Gels douche",
          marque: "ABC Cosmétique",
          tarif: 12.99,
          stock: { qte: 200 },
          actif: 1,
          date_creation: "2024-01-18",
          date_modification: "2024-01-23"
        },
        {
          id: 5,
          id_as400: "ABC005",
          libelle: "Lotion corporelle",
          gencode: "1234567890127",
          famille: "Corps",
          ss_famille: "Lotions",
          marque: "ABC Cosmétique",
          tarif: 16.99,
          stock: { qte: 120 },
          actif: 0,
          date_creation: "2024-01-19",
          date_modification: "2024-01-24"
        }
      ];
      
      setProduits(mockProduits.slice(0, count));
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors du chargement des produits');
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      // Données simulées en attendant que l'API soit configurée
      const mockStats: ProduitsStats = {
        total: 1247,
        enStock: 1058,
        ruptureStock: 189,
        valeurStock: 31845.50
      };
      setStats(mockStats);
    } catch (err) {
      console.error('Erreur lors du chargement des stats:', err);
    }
  };

  const searchProduits = async (searchTerm: string) => {
    if (!searchTerm.trim()) {
      fetchProduits();
      return;
    }

    setLoading(true);
    setError(null);
    try {
      // Simulation de recherche sur les données mock
      const mockProduits: Produit[] = [
        {
          id: 1,
          id_as400: "ABC001",
          libelle: "Crème hydratante visage",
          gencode: "1234567890123",
          famille: "Soins visage",
          ss_famille: "Hydratants",
          marque: "ABC Cosmétique",
          tarif: 24.99,
          stock: { qte: 150 },
          actif: 1,
          date_creation: "2024-01-15",
          date_modification: "2024-01-20"
        },
        {
          id: 2,
          id_as400: "ABC002",
          libelle: "Sérum anti-âge",
          gencode: "1234567890124",
          famille: "Soins visage",
          ss_famille: "Anti-âge",
          marque: "ABC Cosmétique",
          tarif: 45.99,
          stock: { qte: 75 },
          actif: 1,
          date_creation: "2024-01-16",
          date_modification: "2024-01-21"
        },
        {
          id: 3,
          id_as400: "ABC003",
          libelle: "Masque purifiant",
          gencode: "1234567890125",
          famille: "Soins visage",
          ss_famille: "Masques",
          marque: "ABC Cosmétique",
          tarif: 18.99,
          stock: { qte: 0 },
          actif: 1,
          date_creation: "2024-01-17",
          date_modification: "2024-01-22"
        },
        {
          id: 4,
          id_as400: "ABC004",
          libelle: "Gel douche relaxant",
          gencode: "1234567890126",
          famille: "Corps",
          ss_famille: "Gels douche",
          marque: "ABC Cosmétique",
          tarif: 12.99,
          stock: { qte: 200 },
          actif: 1,
          date_creation: "2024-01-18",
          date_modification: "2024-01-23"
        },
        {
          id: 5,
          id_as400: "ABC005",
          libelle: "Lotion corporelle",
          gencode: "1234567890127",
          famille: "Corps",
          ss_famille: "Lotions",
          marque: "ABC Cosmétique",
          tarif: 16.99,
          stock: { qte: 120 },
          actif: 0,
          date_creation: "2024-01-19",
          date_modification: "2024-01-24"
        }
      ];
      
      // Filtrage simple basé sur le terme de recherche
      const filtered = mockProduits.filter(produit => 
        produit.libelle.toLowerCase().includes(searchTerm.toLowerCase()) ||
        produit.id_as400.toLowerCase().includes(searchTerm.toLowerCase()) ||
        produit.famille?.toLowerCase().includes(searchTerm.toLowerCase())
      );
      
      setProduits(filtered);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la recherche');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProduits();
    fetchStats();
  }, []);

  return {
    produits,
    stats,
    loading,
    error,
    fetchProduits,
    searchProduits,
    refreshStats: fetchStats
  };
};
