/**
 * Middleware de validation des données
 */

/**
 * Validation des paramètres requis
 */
const validateRequired = (fields) => {
  return (req, res, next) => {
    const missing = [];
    
    for (const field of fields) {
      if (req.body[field] === undefined || req.body[field] === null || req.body[field] === '') {
        missing.push(field);
      }
    }

    if (missing.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: `Champs manquants: ${missing.join(', ')}`
      });
    }

    next();
  };
};

/**
 * Validation des types de données
 */
const validateTypes = (fieldTypes) => {
  return (req, res, next) => {
    const errors = [];

    for (const [field, expectedType] of Object.entries(fieldTypes)) {
      const value = req.body[field];
      
      if (value !== undefined && value !== null) {
        const actualType = typeof value;
        
        if (expectedType === 'array' && !Array.isArray(value)) {
          errors.push(`${field} doit être un tableau`);
        } else if (expectedType === 'number' && isNaN(Number(value))) {
          errors.push(`${field} doit être un nombre`);
        } else if (expectedType === 'string' && actualType !== 'string') {
          errors.push(`${field} doit être une chaîne de caractères`);
        } else if (expectedType === 'boolean' && actualType !== 'boolean') {
          errors.push(`${field} doit être un booléen`);
        }
      }
    }

    if (errors.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: errors.join(', ')
      });
    }

    next();
  };
};

/**
 * Validation des longueurs de chaînes
 */
const validateLengths = (fieldLengths) => {
  return (req, res, next) => {
    const errors = [];

    for (const [field, { min, max }] of Object.entries(fieldLengths)) {
      const value = req.body[field];
      
      if (value !== undefined && value !== null && typeof value === 'string') {
        if (min !== undefined && value.length < min) {
          errors.push(`${field} doit contenir au moins ${min} caractères`);
        }
        if (max !== undefined && value.length > max) {
          errors.push(`${field} doit contenir au maximum ${max} caractères`);
        }
      }
    }

    if (errors.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: errors.join(', ')
      });
    }

    next();
  };
};

/**
 * Validation des formats (email, téléphone, etc.)
 */
const validateFormats = (fieldFormats) => {
  return (req, res, next) => {
    const errors = [];

    for (const [field, format] of Object.entries(fieldFormats)) {
      const value = req.body[field];
      
      if (value !== undefined && value !== null && value !== '') {
        switch (format) {
          case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
              errors.push(`${field} doit être un email valide`);
            }
            break;
          
          case 'phone':
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phoneRegex.test(value)) {
              errors.push(`${field} doit être un numéro de téléphone valide`);
            }
            break;
          
          case 'date':
            const date = new Date(value);
            if (isNaN(date.getTime())) {
              errors.push(`${field} doit être une date valide`);
            }
            break;
          
          case 'postal_code':
            const postalRegex = /^[0-9]{5}$/;
            if (!postalRegex.test(value)) {
              errors.push(`${field} doit être un code postal valide (5 chiffres)`);
            }
            break;
        }
      }
    }

    if (errors.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: errors.join(', ')
      });
    }

    next();
  };
};

/**
 * Validation des valeurs autorisées
 */
const validateAllowedValues = (fieldValues) => {
  return (req, res, next) => {
    const errors = [];

    for (const [field, allowedValues] of Object.entries(fieldValues)) {
      const value = req.body[field];
      
      if (value !== undefined && value !== null && !allowedValues.includes(value)) {
        errors.push(`${field} doit être une des valeurs suivantes: ${allowedValues.join(', ')}`);
      }
    }

    if (errors.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: errors.join(', ')
      });
    }

    next();
  };
};

/**
 * Validation des IDs (entiers positifs)
 */
const validateIds = (fields) => {
  return (req, res, next) => {
    const errors = [];

    for (const field of fields) {
      const value = req.params[field] || req.body[field];
      
      if (value !== undefined && value !== null) {
        const numValue = Number(value);
        if (isNaN(numValue) || numValue <= 0 || !Number.isInteger(numValue)) {
          errors.push(`${field} doit être un identifiant valide (entier positif)`);
        }
      }
    }

    if (errors.length > 0) {
      return res.status(400).json({
        error: true,
        errorMsg: errors.join(', ')
      });
    }

    next();
  };
};

/**
 * Middleware de validation combiné
 */
const validate = (rules) => {
  return (req, res, next) => {
    const middlewares = [];

    if (rules.required) {
      middlewares.push(validateRequired(rules.required));
    }
    
    if (rules.types) {
      middlewares.push(validateTypes(rules.types));
    }
    
    if (rules.lengths) {
      middlewares.push(validateLengths(rules.lengths));
    }
    
    if (rules.formats) {
      middlewares.push(validateFormats(rules.formats));
    }
    
    if (rules.allowedValues) {
      middlewares.push(validateAllowedValues(rules.allowedValues));
    }
    
    if (rules.ids) {
      middlewares.push(validateIds(rules.ids));
    }

    // Exécution séquentielle des middlewares
    let index = 0;
    const runNext = () => {
      if (index < middlewares.length) {
        middlewares[index++](req, res, runNext);
      } else {
        next();
      }
    };
    
    runNext();
  };
};

module.exports = {
  validateRequired,
  validateTypes,
  validateLengths,
  validateFormats,
  validateAllowedValues,
  validateIds,
  validate
};
