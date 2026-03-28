# 🌾 Benguet Agricultural Crop Production Prediction System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.0-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Python-ML_API-blue?style=for-the-badge&logo=python" alt="Python ML">
  <img src="https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind">
  <img src="https://img.shields.io/badge/PostgreSQL-Database-336791?style=for-the-badge&logo=postgresql" alt="PostgreSQL">
</p>

<p align="center">
  <strong>An Intelligent Decision Support System for Agricultural Planning in Benguet Province</strong>
</p>

---

## 📋 Table of Contents

1. [Executive Summary](#-executive-summary)
2. [Problem Statement](#-problem-statement)
3. [Solution Overview](#-solution-overview)
4. [Key Features](#-key-features)
5. [Target Users](#-target-users)
6. [System Architecture](#-system-architecture)
7. [Technology Stack](#-technology-stack)
8. [Data Sources](#-data-sources)
9. [Prediction Model](#-prediction-model)
10. [User Roles & Access](#-user-roles--access)
11. [Installation & Setup](#-installation--setup)
12. [Usage Guide](#-usage-guide)
13. [API Documentation](#-api-documentation)
14. [Benefits & Impact](#-benefits--impact)
15. [Future Enhancements](#-future-enhancements)
16. [Support & Contact](#-support--contact)

---

## 📊 Executive Summary

The **Benguet Agricultural Crop Production Prediction System** is a web-based decision support platform designed to assist farmers, agricultural technicians, and policymakers in Benguet Province with data-driven crop production planning.

Using **Machine Learning algorithms** trained on historical crop production data from the **13 municipalities of Benguet**, the system provides:

- **Production Predictions** - Estimate crop yields based on area planted, farm type, and environmental factors
- **Multi-Year Forecasts** - Project future production trends for strategic planning
- **Interactive GIS Mapping** - Visualize agricultural data across municipalities
- **Farmer Community Forum** - Knowledge sharing platform for farmers
- **Comprehensive Reports** - Generate PDF/CSV reports for documentation

---

## ❓ Problem Statement

### Challenges Facing Benguet Farmers

1. **Unpredictable Yields** - Farmers lack tools to estimate crop production accurately
2. **Poor Planning** - Decisions are made based on intuition rather than data
3. **Market Oversupply/Undersupply** - Without forecasting, market imbalances occur
4. **Limited Data Access** - Historical crop data is not easily accessible to farmers
5. **Climate Adaptation** - Need for data-driven response to changing weather patterns

### Impact on Agriculture

- Financial losses due to poor crop selection
- Inefficient resource allocation
- Difficulty in accessing agricultural support programs
- Limited ability to plan for market demands

---

## 💡 Solution Overview

Our system addresses these challenges by providing:

| Feature | Benefit |
|---------|---------|
| **ML-Powered Predictions** | Accurate yield estimates based on historical data |
| **User-Friendly Interface** | Accessible to farmers with varying tech literacy |
| **Real-Time Forecasting** | 5-year production projections for planning |
| **Interactive Maps** | Visual representation of municipal crop data |
| **Community Forum** | Peer-to-peer knowledge sharing |
| **Farmer Calendar** | Personal farm activity planner with reminders |

---

## ✨ Key Features

### 1. 🔮 Crop Production Prediction

Predict crop production using 6 key input parameters:

- **Municipality** - 13 municipalities of Benguet
- **Farm Type** - Irrigated or Rainfed
- **Year** - Target production year
- **Month** - Planting/harvest month
- **Crop Type** - 10+ vegetable varieties
- **Area Planted** - Hectares to be planted

**Output:**
- Predicted Production (Metric Tons)
- Confidence Score (%)
- Historical Comparison

### 2. 📈 Multi-Year Forecasting

Generate production forecasts for up to 5 years:
- Trend analysis based on historical patterns
- Municipality-specific projections
- Crop-by-crop breakdown
- Visualization with interactive charts

### 3. 🗺️ Interactive GIS Map

Visualize agricultural data across Benguet:
- Municipality boundaries with GeoJSON overlay
- Crop production heatmaps
- Click-to-view municipal statistics
- Filter by crop type and year

### 4. 💬 Farmer Community Forum

Knowledge-sharing platform featuring:
- Categorized discussions (Crops, Weather, Markets, etc.)
- Crop and municipality tags
- Voting system for best answers
- Search and filter functionality

### 5. 📅 Farmer Calendar

Personal agricultural planner:
- Schedule planting and harvesting activities
- Set reminders for farm tasks
- Track completed activities
- View upcoming events

### 6. 📊 Reports & Analytics (Admin)

Comprehensive reporting tools:
- Production Summary Reports
- Prediction Analytics
- Comparative Analysis (Municipality/Crop)
- User Activity Reports
- Export to PDF/CSV

### 7. 🔐 User Management (Admin)

Administrative controls:
- User registration approval
- Role management (Admin/Farmer)
- Activity monitoring
- Data import/export

---

## 👥 Target Users

### Primary Users

| User Type | Description | Key Needs |
|-----------|-------------|-----------|
| **Farmers** | Local vegetable farmers in Benguet | Production estimates, planning tools |
| **Agricultural Technicians** | DA field officers | Data collection, farmer assistance |
| **Barangay Agri Officers** | Local agricultural coordinators | Municipal crop monitoring |

### Secondary Users

| User Type | Description | Key Needs |
|-----------|-------------|-----------|
| **Municipal Agriculturists** | Municipal planning officers | Reports, trend analysis |
| **Provincial Agriculturist** | Provincial DA office | Policy planning, resource allocation |
| **Researchers** | Academic institutions | Historical data access |

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                           │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐ │
│  │   Farmer    │ │    Admin    │ │  Interactive│ │   Forum   │ │
│  │  Dashboard  │ │  Dashboard  │ │     Map     │ │           │ │
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION                          │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Controllers: Prediction | Map | Forum | Reports | Users    ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Services: CropPredictionService | Caching | Queue Jobs     ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Models: User | CropProduction | Prediction | Forum         ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
                    │                       │
                    ▼                       ▼
┌─────────────────────────────┐   ┌─────────────────────────────┐
│      PYTHON ML API          │   │        DATABASE             │
│  ┌───────────────────────┐  │   │  ┌───────────────────────┐  │
│  │  Prediction Engine    │  │   │  │    MySQL/PostgreSQL   │  │
│  │  (68.17% Accuracy)    │  │   │  │                       │  │
│  ├───────────────────────┤  │   │  │  - crop_production    │  │
│  │  Forecast Generator   │  │   │  │  - predictions        │  │
│  ├───────────────────────┤  │   │  │  - users              │  │
│  │  Model Training       │  │   │  │  - forum_posts        │  │
│  └───────────────────────┘  │   │  │  - forum_comments     │  │
└─────────────────────────────┘   │  └───────────────────────┘  │
                                  └─────────────────────────────┘
```

---

## 🛠️ Technology Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 12.0 | Web application framework |
| **Laravel Sanctum** | 4.0 | API authentication |
| **Laravel Breeze** | 2.3 | Authentication scaffolding |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Tailwind CSS** | 3.1+ | Utility-first CSS framework |
| **Alpine.js** | 3.4+ | Lightweight JavaScript framework |
| **Vite** | 7.0 | Frontend build tool |
| **Leaflet.js** | - | Interactive maps |
| **Chart.js** | - | Data visualization |

### Machine Learning
| Technology | Purpose |
|------------|---------|
| **Python** | ML model development |
| **Flask** | ML API server |
| **Scikit-learn** | ML algorithms |
| **Pandas** | Data processing |

### Database
| Technology | Purpose |
|------------|---------|
| **MySQL/PostgreSQL** | Primary database |
| **Redis** | Caching (optional) |

### Other Tools
| Technology | Purpose |
|------------|---------|
| **DomPDF** | PDF report generation |
| **Maatwebsite Excel** | Excel import/export |
| **GeoJSON** | Municipality boundaries |

---

## 📁 Data Sources

### Historical Crop Production Data

The system is trained on historical crop production data from the **Department of Agriculture - Cordillera Administrative Region (DA-CAR)**.

**Data Coverage:**
- **Municipalities:** 13 municipalities of Benguet Province
- **Time Period:** 2015-2024 (expandable)
- **Crops Covered:** 10+ Highland vegetables
- **Records:** 50,000+ data points

### Crops Included

| Category | Crops |
|----------|-------|
| **Leafy Vegetables** | Cabbage, Lettuce, Chinese Cabbage, Broccoli, Cauliflower |
| **Root Crops** | Carrots, White Potato |
| **Legumes** | Garden Peas, Snap Beans |
| **Fruits/Others** | Sweet Pepper, Tomato |

### Municipalities Covered

| Municipality | Municipality | Municipality |
|--------------|--------------|--------------|
| Atok | Bakun | Bokod |
| Buguias | Itogon | Kabayan |
| Kapangan | Kibungan | La Trinidad |
| Mankayan | Sablan | Tuba |
| Tublay | | |

---

## 🤖 Prediction Model

### Model Specifications

| Attribute | Value |
|-----------|-------|
| **Algorithm** | Random Forest Regressor |
| **Accuracy** | 68.17% R² Score |
| **Features** | 6 input parameters |
| **Training Data** | 50,000+ historical records |

### Input Features

1. **MUNICIPALITY** - Categorical (13 options)
2. **FARM_TYPE** - Categorical (Irrigated/Rainfed)
3. **YEAR** - Numeric (2020-2030)
4. **MONTH** - Categorical (January-December)
5. **CROP** - Categorical (10+ options)
6. **AREA_PLANTED_HA** - Numeric (hectares)

### Output

- **Predicted Production (MT)** - Estimated yield in metric tons
- **Confidence Score (%)** - Model confidence level

### Model Performance

```
Training Set R²: 0.7234
Test Set R²: 0.6817
Mean Absolute Error: 12.45 MT
Root Mean Square Error: 18.72 MT
```

---

## 🔑 User Roles & Access

### Role-Based Access Control

| Feature | Farmer | Admin |
|---------|:------:|:-----:|
| View Dashboard | ✅ | ✅ |
| Make Predictions | ✅ | ✅ |
| View Prediction History | ✅ | ✅ |
| Interactive Map | ✅ | ✅ |
| Farmer Calendar | ✅ | ✅ |
| Community Forum | ✅ | ✅ |
| Crop Data Management | ❌ | ✅ |
| User Management | ❌ | ✅ |
| Generate Reports | ❌ | ✅ |
| Import/Export Data | ❌ | ✅ |

---

## 🚀 Installation & Setup

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ or PostgreSQL 14+
- Python 3.9+ (for ML API)
- XAMPP/WAMP/Laravel Valet (optional)

### Step 1: Clone Repository

```bash
git clone https://github.com/your-repo/benguet-crop-prediction.git
cd benguet-crop-prediction
```

### Step 2: Install Dependencies

```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crop_prediction
DB_USERNAME=root
DB_PASSWORD=your_password

# ML API Configuration
ML_API_URL=http://127.0.0.1:5000
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Seed Database (Optional)

```bash
php artisan db:seed
```

### Step 7: Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### Step 8: Start Development Server

```bash
# Start all services (recommended)
composer dev

# Or manually:
php artisan serve
php artisan queue:listen
npm run dev
```

### Step 9: Start ML API (Separate Terminal)

```bash
cd ml-api
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

---

## 📖 Usage Guide

### For Farmers

#### Making a Prediction

1. **Login** to your account
2. Navigate to **Predictions** in the sidebar
3. Fill in the prediction form:
   - Select your municipality
   - Choose farm type (Irrigated/Rainfed)
   - Select target year and month
   - Choose crop type
   - Enter area to be planted (hectares)
4. Click **"Predict Production"**
5. View results with confidence score

#### Using the Calendar

1. Navigate to **My Calendar**
2. Click on a date to add an event
3. Fill in event details:
   - Title (e.g., "Plant Cabbage")
   - Description
   - Start/End date
   - Set reminder
4. Save and track your farm activities

#### Community Forum

1. Navigate to **Forum**
2. Browse existing discussions by category
3. Filter by crop or municipality
4. Create a new post to ask questions
5. Vote on helpful answers

### For Administrators

#### Importing Crop Data

1. Navigate to **Admin > Crop Data**
2. Click **Import Data**
3. Upload Excel file with crop production data
4. Map columns to database fields
5. Review and confirm import

#### Generating Reports

1. Navigate to **Admin > Reports**
2. Select report type:
   - Production Summary
   - Prediction Analytics
   - Comparative Analysis
   - User Activity
3. Apply filters (year, municipality, crop)
4. Export as PDF or CSV

---

## 🔌 API Documentation

### Authentication

All API endpoints require authentication via Laravel Sanctum.

```bash
# Login and get token
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

### Prediction Endpoints

#### Make Prediction

```bash
POST /predictions/predict
Authorization: Bearer {token}
Content-Type: application/json

{
  "municipality": "LA TRINIDAD",
  "farm_type": "IRRIGATED",
  "year": 2026,
  "month": 3,
  "crop": "CABBAGE",
  "area_planted": 10.5
}
```

**Response:**
```json
{
  "success": true,
  "prediction": {
    "production_mt": 149.43,
    "confidence_score": 0.85
  },
  "prediction_id": 123,
  "saved_to_history": true
}
```

#### Generate Forecast

```bash
POST /predictions/forecast
Authorization: Bearer {token}
Content-Type: application/json

{
  "municipality": "BUGUIAS",
  "crop": "POTATO",
  "forecast_years": 5
}
```

#### Batch Predictions

```bash
POST /predictions/batch-predict
Authorization: Bearer {token}
Content-Type: application/json

{
  "predictions": [
    {
      "municipality": "ATOK",
      "farm_type": "RAINFED",
      "year": 2026,
      "month": 5,
      "crop": "CARROTS",
      "area_planted": 5.0
    },
    // ... more predictions
  ]
}
```

### Available Options

```bash
GET /predictions/options
Authorization: Bearer {token}
```

**Response:**
```json
{
  "municipalities": ["ATOK", "BAKUN", ...],
  "crops": ["Cabbage", "Carrots", ...],
  "farm_types": ["IRRIGATED", "RAINFED"],
  "years": [2020, 2021, ..., 2030],
  "months": ["January", "February", ...]
}
```

---

## 🎯 Benefits & Impact

### For Farmers

| Benefit | Impact |
|---------|--------|
| **Better Planning** | Estimate yields before planting |
| **Risk Reduction** | Make data-driven crop choices |
| **Market Alignment** | Plan production for market demand |
| **Resource Efficiency** | Optimize land and input usage |
| **Knowledge Sharing** | Learn from peer experiences |

### For Department of Agriculture

| Benefit | Impact |
|---------|--------|
| **Data-Driven Policy** | Evidence-based agricultural planning |
| **Resource Allocation** | Target support where needed |
| **Production Monitoring** | Track provincial crop output |
| **Farmer Engagement** | Digital platform for farmer services |
| **Trend Analysis** | Identify emerging patterns |

### Measurable Outcomes

- **Prediction Accuracy:** 68.17% (continuously improving)
- **Farmer Adoption:** Targeting 500+ users in Year 1
- **Data Coverage:** 13 municipalities, 10+ crops
- **Time Savings:** Instant predictions vs. manual estimation

---

## 🔮 Future Enhancements

### Phase 2 (Planned)

- [ ] **Weather Integration** - Incorporate climate data for better predictions
- [ ] **Mobile App** - Android/iOS application for farmers
- [ ] **SMS Notifications** - Alerts for non-smartphone users
- [ ] **Market Price Integration** - Link predictions to price forecasts
- [ ] **Pest & Disease Alerts** - Integration with pest monitoring

### Phase 3 (Vision)

- [ ] **Satellite Imagery** - Remote sensing for crop monitoring
- [ ] **IoT Sensors** - Real-time soil and weather data
- [ ] **AI Chatbot** - Natural language query support
- [ ] **Multi-Province Expansion** - Extend to other CAR provinces
- [ ] **Cooperative Integration** - Link with farmer cooperatives

---

## 🆘 Support & Contact

### Technical Support

For technical issues or bug reports:
- **Email:** support@benguetcrops.ph
- **Phone:** (074) XXX-XXXX

### Training & Assistance

For user training and onboarding:
- Contact your Municipal Agricultural Office
- Request demonstration via DA-CAR

### Feedback & Suggestions

We value your input! Submit feedback through:
- In-app feedback form
- Community forum suggestions category
- Email to feedback@benguetcrops.ph

---

## 📜 License

This project is developed for the **Department of Agriculture - Cordillera Administrative Region** and is intended for use by authorized agricultural stakeholders in Benguet Province.

---

## 🙏 Acknowledgments

- **Department of Agriculture - CAR** - Data provision and guidance
- **Provincial Government of Benguet** - Support and collaboration
- **Municipal Agricultural Offices** - Data collection and validation
- **Farmers of Benguet** - Inspiration and feedback

---

<p align="center">
  <strong>🌱 Empowering Benguet Farmers with Data-Driven Agriculture 🌱</strong>
</p>

<p align="center">
  Developed with ❤️ for the farming communities of Benguet Province
</p>
