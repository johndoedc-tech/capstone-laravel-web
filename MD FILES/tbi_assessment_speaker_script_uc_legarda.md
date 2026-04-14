# TBI Assessment Speaker Script
## University of the Cordilleras - Legarda

### Suggested Duration: 7 to 9 minutes

## 1. Opening (30 to 45 seconds)
Good day, panel. We are presenting Harviana, a data-driven agricultural decision support system built for Benguet farmers and agricultural stakeholders.

Harviana combines crop production prediction, interactive municipal mapping, weather intelligence, farmer planning tools, and an AI assistant in one platform. Our goal is simple: help farmers make better decisions before they plant, not after losses happen.

## 2. Problem Statement (45 to 60 seconds)
In Benguet, many planning decisions are still based on intuition, scattered records, or delayed reports.

This creates three major problems:
1. Uncertain yields and weak production planning.
2. Risk of oversupply or undersupply in specific crops.
3. Limited access to practical, localized decision support for farmers.

At the same time, data exists. But it is not always transformed into timely, understandable guidance for day-to-day farm decisions.

## 3. What Harviana Solves (60 to 90 seconds)
Harviana turns historical crop production data into practical planning insights.

Core value proposition:
1. Predict expected production using key farm inputs.
2. Visualize municipal trends through an interactive map.
3. Add weather context for local farm decision-making.
4. Support community learning through forum and shared discussions.
5. Assist farmers through an AI chatbot for crop, map, and prediction questions.

In short, Harviana brings analytics, planning, and advisory support into one workflow.

## 4. Product Walkthrough (2 minutes)
When a farmer logs in, they can:
1. Set preferences such as municipality and crop interests.
2. Use the prediction module by entering municipality, farm type, year, month, crop, and area planted.
3. Receive a projected output in metric tons with confidence information and history.
4. Open the interactive map to compare crop behavior across municipalities.
5. View weather context through the map weather API integration for local planning.
6. Use calendar tools to schedule and track farm tasks.
7. Ask Harviana Assistant for quick decision support in plain language.

For admin users, the system adds:
1. Crop data management and import workflows.
2. User and activity monitoring.
3. Reports for production summary, prediction analytics, and comparative views.

## 5. Technology and AI Deep Dive (90 seconds)
Harviana uses a Laravel-based web platform connected to a separate Python ML API service.

Our prediction engine is based on Random Forest regression, trained from Benguet crop records across 13 municipalities and 10+ crops. The current benchmark is around 68.17 percent R-squared.

The system includes:
1. Prediction and history storage for traceability.
2. Queue-ready architecture for heavier workloads.
3. Cache-backed responses and reliability controls.
4. Gemini-powered assistant with safeguards for duplicate calls, in-flight lock control, cooldown handling, and fallback behavior.

This architecture is already deployable and currently hosted in a cloud environment using Railway-compatible deployment flow.

## 6. Innovation and Defensibility (60 seconds)
What makes Harviana different is the combination of localized data, practical workflows, and explainable outputs in a single farmer-facing system.

It is not just an AI chatbot and not just a prediction calculator. It is an integrated operations tool that connects:
1. Prediction,
2. Location context,
3. Weather context,
4. Task planning,
5. And farmer knowledge sharing.

This makes adoption more realistic because users can act on recommendations immediately.

## 7. Impact Potential (45 to 60 seconds)
Expected impact areas:
1. Better pre-planting decisions.
2. Reduced avoidable losses from poor planning.
3. More coordinated production insights at municipal level.
4. Faster technical support access through AI-assisted guidance.

For institutions, Harviana can support evidence-based planning, extension services, and reporting.

## 8. Current Stage and Next Steps (60 seconds)
Current stage:
1. Core modules are functional: prediction, map, calendar, forum, chatbot, and admin analytics.
2. Reliability improvements are active, including chatbot request controls and response handling.

Next steps:
1. Improve model accuracy with expanded features and retraining cycles.
2. Strengthen weather-driven recommendations and proactive alerts.
3. Expand bilingual and farmer onboarding support.
4. Enhance production hardening and scale readiness.

## 9. Closing (20 to 30 seconds)
Harviana is our step toward practical, data-informed agriculture for Benguet.

For this TBI assessment, we present it as a scalable, impact-oriented agri-tech platform with clear technical feasibility and social value.

Thank you, and we welcome your questions.

---

## Optional Q and A Bridging Line
If I may, after this overview we can quickly demonstrate one live farmer workflow from preference setup to prediction to chatbot recommendation for a specific municipality in Benguet.
