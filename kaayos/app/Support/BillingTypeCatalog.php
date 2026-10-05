<?php

namespace App\Support;

/**
 * Billing-type classification for the services catalog.
 *
 * fixed  = scoped deliverable, flat price regardless of duration
 * hourly = duration varies with size/conditions, price = hourly_rate x hours
 * either = both modes make sense; client picks at booking time
 *
 * Keys are service names (matches services.name). 'Furniture Assembly'
 * exists in both Carpentry and General and is fixed in both.
 */
class BillingTypeCatalog
{
    public const MAP = [
        // Plumbing
        'Leak Repair' => 'hourly',
        'Pipe Installation' => 'either',
        'Water Heater Service' => 'either',
        'Drain Cleaning' => 'hourly',
        'Toilet Repair & Installation' => 'fixed',
        'Faucet Replacement' => 'fixed',
        'Sewer Line Cleaning' => 'hourly',
        'Garbage Disposal Repair' => 'fixed',

        // Electrical
        'Electrical Inspection' => 'fixed',
        'Wiring & Rewiring' => 'hourly',
        'Lighting Installation' => 'fixed',
        'Outlet & Switch Repair' => 'fixed',
        'Ceiling Fan Installation' => 'fixed',
        'Panel Upgrade' => 'hourly',
        'Generator Repair' => 'either',
        'Smart Home Setup' => 'hourly',

        // Cleaning
        'Deep Cleaning' => 'hourly',
        'Move-in/out Cleaning' => 'hourly',
        'Window Cleaning' => 'either',
        'Carpet Shampooing' => 'either',
        'Upholstery Cleaning' => 'either',
        'Post-Construction Cleaning' => 'hourly',
        'Office Cleaning' => 'hourly',
        'Pressure Washing' => 'hourly',

        // Carpentry
        'Furniture Assembly' => 'fixed',
        'Cabinet Installation' => 'fixed',
        'Custom Shelving' => 'fixed',
        'Door Repair & Installation' => 'fixed',
        'Deck Repair' => 'hourly',
        'Trim & Molding' => 'hourly',
        'Drywall Repair' => 'fixed',
        'Kitchen Cabinet Refacing' => 'hourly',

        // Painting
        'Interior Painting' => 'hourly',
        'Exterior Painting' => 'hourly',
        'Cabinet Refinishing' => 'fixed',
        'Fence Painting' => 'hourly',
        'Ceiling Painting' => 'hourly',
        'Wallpaper Removal' => 'hourly',
        'Garage Floor Coating' => 'either',

        // Aircon
        'AC Cleaning' => 'fixed',
        'AC Repair' => 'either',
        'AC Installation' => 'fixed',
        'Gas Refill' => 'fixed',
        'Duct Cleaning' => 'hourly',
        'Compressor Repair' => 'either',
        'Filter Replacement' => 'fixed',

        // Landscaping
        'Lawn Mowing' => 'either',
        'Tree Trimming' => 'hourly',
        'Hedge Trimming' => 'fixed',
        'Planting & Gardening' => 'fixed',
        'Yard Cleanup' => 'hourly',
        'Garden Design' => 'either',
        'Lot Clearing' => 'hourly',

        // Laundry
        'Wash & Fold' => 'fixed',
        'Dry Cleaning' => 'fixed',
        'Ironing & Pressing' => 'fixed',
        'Pick-up & Delivery' => 'fixed',
        'Curtain Cleaning' => 'fixed',
        'Blanket & Comforter Wash' => 'fixed',

        // Pest Control
        'General Fumigation' => 'fixed',
        'Rodent Control' => 'fixed',
        'Termite Treatment' => 'fixed',
        'Mosquito Control' => 'fixed',
        'Ant & Roach Removal' => 'fixed',
        'Preventive Spraying' => 'fixed',

        // Appliance Repair
        'Refrigerator Repair' => 'fixed',
        'Washing Machine Repair' => 'fixed',
        'Oven & Stove Repair' => 'fixed',
        'Water Dispenser Repair' => 'fixed',
        'Electric Fan Repair' => 'fixed',
        'Microwave Repair' => 'fixed',
        'Dryer Repair' => 'fixed',

        // Masonry
        'Tiling' => 'hourly',
        'Concrete Repair' => 'hourly',
        'Brick & Block Work' => 'hourly',
        'Waterproofing' => 'hourly',
        'Wall Repointing' => 'hourly',
        'Pavement & Walkway' => 'hourly',

        // Personal Care
        'Home Massage' => 'fixed',
        'Manicure' => 'fixed',
        'Pedicure' => 'fixed',
        'Haircut & Blow-dry' => 'fixed',
        'Facial' => 'fixed',
        'Waxing' => 'fixed',

        // Moving Services
        'Packing Assistance' => 'hourly',
        'Loading & Unloading' => 'hourly',
        'Local Transport' => 'fixed',
        'Heavy Item Moving' => 'hourly',
        'Storage Assistance' => 'hourly',

        // Roofing
        'Roof Installation' => 'hourly',
        'Roof Repair' => 'hourly',
        'Gutter Cleaning' => 'fixed',

        // Welding
        'Welding Repair' => 'either',
        'Metal Fabrication' => 'hourly',
        'Gate & Railing Repair' => 'either',

        // General
        'General Repair' => 'hourly',
        'Home Maintenance' => 'hourly',
    ];
}
